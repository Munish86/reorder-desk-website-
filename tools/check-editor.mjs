// Checks that every page, post, template, template part and pattern parses as valid blocks in the WordPress editor
// (no "This block contains unexpected or invalid content" for the owner).
// Usage: node tools/check-editor.mjs      (reads the login from local-login.txt)
import fs from "node:fs";
import path from "node:path";
import { createRequire } from "node:module";
import { fileURLToPath } from "node:url";

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, "..");
const base = ["../../restock-app", "../../shopify-bulk-uploader-app"].map((p) => path.resolve(here, p)).find((p) => fs.existsSync(path.join(p, "node_modules/playwright-core")));
const { chromium } = createRequire(path.join(base, "package.json"))("playwright-core");
const executablePath = [process.env.CHROME_PATH, "C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe", "C:/Program Files/Google/Chrome/Application/chrome.exe"].find((p) => p && fs.existsSync(p));

const site = process.env.SITE || "http://localhost:8088";
const login = fs.readFileSync(path.join(root, "local-login.txt"), "utf8");
const user = /User: (.+)/.exec(login)[1].trim();
const pass = /Password: (.+)/.exec(login)[1].trim();

// Theme files that hold block markup (patterns: PHP stripped to its markup, PHP echo replaced by a URL)
const themeDir = path.join(root, "theme/reorder-desk");
const files = [];
for (const dir of ["templates", "parts", "patterns"]) {
  for (const f of fs.readdirSync(path.join(themeDir, dir))) {
    let text = fs.readFileSync(path.join(themeDir, dir, f), "utf8");
    if (f.endsWith(".php")) text = text.replace(/<\?php[\s\S]*?\?>/g, "/x.webp").replace(/^\/x\.webp\s*/, "");
    files.push({ name: `${dir}/${f}`, text });
  }
}

const browser = await chromium.launch({ executablePath });
const page = await browser.newPage({ viewport: { width: 1400, height: 900 } });
await page.goto(`${site}/wp-login.php`, { timeout: 120000 });
await page.fill("#user_login", user);
await page.fill("#user_pass", pass);
await Promise.all([page.waitForNavigation({ timeout: 120000 }), page.click("#wp-submit")]);
await page.goto(`${site}/wp-admin/post-new.php?post_type=page`, { timeout: 180000 });
await page.waitForFunction(() => window.wp?.blocks?.getBlockTypes?.().length > 60 && window.wp?.apiFetch, null, { timeout: 180000 });

const result = await page.evaluate(async (files) => {
  const items = [];
  for (const type of ["pages", "posts"]) {
    const list = await wp.apiFetch({ path: `/wp/v2/${type}?per_page=100&context=edit&status=publish` });
    for (const p of list) items.push({ name: `${type}/${p.slug}`, text: p.content.raw });
  }
  items.push(...files);
  const out = [];
  const walk = (blocks, name) => {
    for (const b of blocks) {
      if (b.isValid === false) {
        const issues = (b.validationIssues || []).map((i) => (i.args || []).map(String).join(" ").slice(0, 400)).join(" | ");
        out.push(`${name}: ${b.name} ${issues}`);
      }
      if (b.name === "core/missing") out.push(`${name}: unknown block ${b.attributes.originalName}`);
      walk(b.innerBlocks || [], name);
    }
  };
  for (const it of items) walk(wp.blocks.parse(it.text), it.name);
  return { checked: items.map((i) => i.name), invalid: out };
}, files);

console.log(`checked ${result.checked.length}: ${result.checked.join(", ")}`);
if (result.invalid.length) {
  console.log("INVALID:");
  for (const l of result.invalid) console.log("  " + l);
} else {
  console.log("all blocks valid");
}
await browser.close();
process.exit(result.invalid.length ? 1 : 0);
