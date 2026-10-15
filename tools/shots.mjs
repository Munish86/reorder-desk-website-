// Screenshots of the local site at desktop and phone width, and a check for broken pages.
// Usage: node tools/shots.mjs [path ...]      e.g.  node tools/shots.mjs pricing/ blog/   (no args = home page)
// Needs Chrome or Edge, and playwright-core from a sibling project (restock-app or shopify-bulk-uploader-app).
// Saves PNGs in tools/.shots/ and prints, per page: HTTP status, console errors, failed requests, horizontal overflow.
import fs from "node:fs";
import path from "node:path";
import { createRequire } from "node:module";
import { fileURLToPath } from "node:url";

const here = path.dirname(fileURLToPath(import.meta.url));
const projects = ["../../restock-app", "../../shopify-bulk-uploader-app"].map((p) => path.resolve(here, p));
const base = projects.find((p) => fs.existsSync(path.join(p, "node_modules/playwright-core")));
if (!base) throw new Error("playwright-core not found in restock-app or shopify-bulk-uploader-app");
const { chromium } = createRequire(path.join(base, "package.json"))("playwright-core");

const executablePath = [
  process.env.CHROME_PATH,
  "C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe", // Chrome headless hangs on this PC
  "C:/Program Files/Google/Chrome/Application/chrome.exe",
].find((p) => p && fs.existsSync(p));

const site = process.env.SITE || "http://localhost:8088";
// Paths may be given without the leading slash ("pricing/"), which Git Bash leaves alone
const paths = (process.argv.slice(2).length ? process.argv.slice(2) : ["/"]).map((p) => (p.startsWith("/") ? p : "/" + p));
const out = path.join(here, ".shots");
fs.mkdirSync(out, { recursive: true });

const browser = await chromium.launch({ executablePath });
let problems = 0;
for (const [label, viewport] of [["desktop", { width: 1440, height: 900 }], ["phone", { width: 390, height: 844 }]]) {
  const context = await browser.newContext({ viewport, deviceScaleFactor: 1 });
  for (const p of paths) {
    const page = await context.newPage();
    const issues = [];
    page.on("console", (m) => m.type() === "error" && issues.push(`console: ${m.text()}`));
    page.on("pageerror", (e) => issues.push(`pageerror: ${e.message}`));
    page.on("requestfailed", (r) => issues.push(`failed: ${r.url()}`));
    page.on("response", (r) => r.status() >= 400 && issues.push(`${r.status()}: ${r.url()}`));
    const res = await page.goto(site + p, { waitUntil: "load", timeout: 90000 });
    await page.screenshot({ path: path.join(out, `${label}-${p.replace(/[^a-z0-9]+/gi, "-").replace(/^-|-$/g, "") || "home"}-top.png`) });
    // Scroll through so lazy-loaded images load before the full-page shot
    await page.evaluate(async () => {
      for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 60)); }
      window.scrollTo(0, 0);
    });
    await page.waitForLoadState("networkidle", { timeout: 15000 }).catch(() => {});
    await page.waitForTimeout(500);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    if (overflow > 0) issues.push(`horizontal overflow: ${overflow}px`);
    const name = `${label}-${p.replace(/[^a-z0-9]+/gi, "-").replace(/^-|-$/g, "") || "home"}.png`;
    await page.screenshot({ path: path.join(out, name), fullPage: true });
    console.log(`${issues.length ? "FAIL" : "ok  "} ${label} ${p} ${res?.status()} -> ${name}`);
    for (const i of issues) console.log("     " + i);
    problems += issues.length;
    await page.close();
  }
  await context.close();
}
await browser.close();
process.exit(problems ? 1 : 0);
