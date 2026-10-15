# Reorder Desk website: owner guide (Hinglish)

Ye WordPress website Reorder Desk app ki marketing ke liye hai. Abhi ye sirf tumhare computer par chalti hai.

## 1. Site kholna

1. XAMPP Control Panel kholo, **Apache** aur **MySQL** dono Start karo.
2. Browser mein: **http://localhost:8088** (website)
3. Admin panel: **http://localhost:8088/wp-admin/**
   - User aur password: `reorder-desk-website/local-login.txt` mein hain. Ye file kisi ko mat bhejna.

Note: XAMPP mein PHP ka "OPcache" band hai, isliye local site thodi slow khulti hai (har page 5-20 second).
Fast karne ke liye `C:\xampp\php\php.ini` mein ye do lines dhoondo aur aage ka `;` hata do, phir Apache restart karo:
```
;zend_extension=opcache      ->  zend_extension=opcache
;opcache.enable=1            ->  opcache.enable=1
```
(Ye XAMPP ki baaki sites ko bhi fast karega; kuch nahi todega.)

## 2. Website mein kya hai

| Page | Address |
|---|---|
| Home | `/` |
| Features | `/features/` |
| How forecasts work | `/how-it-works/` |
| Pricing (Free / Pro $29) | `/pricing/` |
| Stocky alternative (SEO page) | `/stocky-alternative/` |
| Help centre | `/help/` |
| Early access (form) | `/early-access/` |
| Contact (form) | `/contact/` |
| Privacy policy, Terms | `/privacy-policy/`, `/terms/` |
| Blog (4 posts) | `/blog/` |

## 3. Text ya page badalna

- **Koi page:** wp-admin > Pages > page par click > text par click karke badlo > **Save**.
- **Header / footer:** wp-admin > Appearance > Editor > Patterns > Template parts > Header ya Footer.
- **Naya section daalna:** page mein `+` > Patterns > "Reorder Desk" (CTA band, feature cards, FAQ, screenshot ke saath text).
- **Price badalna:** Home, Pricing aur Stocky page teeno mein "$29" likha hai. App mein bhi `restock-app/app/lib/plans.ts`
  badalna padega (Claude kar dega).

## 4. Naya blog post

1. wp-admin > Posts > Add New.
2. Title, text likho. Right side: **Category** (Guides / Stocky), **Featured image** (cover, 1600x900),
   **Excerpt** (2 line summary: blog list mein aur post ke title ke neeche dikhti hai).
3. Neeche Yoast box mein "Meta description" (Google mein dikhti hai, ~155 characters).
4. **Publish**.

Achhe topics (log Google par ye search karte hain): "Shopify purchase order app", "safety stock formula",
"Shopify low stock alert", "inventory forecasting for small business", "how much stock to order".

## 5. Form entries (early access, contact)

- wp-admin > **Leads**: saari entries; "Download CSV" se Excel mein.
- Local computer se email nahi jaati, par entry hamesha Leads mein save hoti hai.
- Settings > **Reorder Desk site**: support email aur "new sign-up kis email par aaye" yahan bharo.

## 6. App live hone ke din (sirf 1 setting)

Settings > Reorder Desk site > **App Store URL** mein app ka link daalo (jaise `https://apps.shopify.com/...`) > Save.
Website ke saare "Get early access" buttons apne aap **"Install free on Shopify"** ban jayenge aur App Store par le jayenge.
Phir:
- Home ke FAQ "When can I install it?" aur `/early-access/` page ka text badal do (ya Claude se karwa lo).
- Leads > Download CSV, aur sabko ek email bhejo ki app live hai (privacy policy mein yahi vaada hai: sirf ek email).

## 7. Website live karna (domain + hosting)

Abhi site sirf tumhare PC par hai; Google aur customers ise nahi dekh sakte.

1. **Naam pakka karo (zaroori, neeche dekho).** Phir domain lo (~₹800-1,200/saal).
2. **Hosting:** koi bhi WordPress hosting jismein PHP 8.2+ aur MySQL ho, aur SSL (https) free mile. Shared hosting
   ₹200-800/month kaafi hai. Site domain ke root par honi chahiye (jaise `reorderdesk.xyz`, ya `www.` / koi subdomain);
   kisi folder mein (`domain.com/site/`) nahi, warna links toot jayenge.
3. **Shift karna:** local wp-admin mein plugin **All-in-One WP Migration** install karo > Export > File. Hosting par naya
   WordPress install karo, wahi plugin daalo > Import > wo file. (Ya Claude ko hosting ka access do, Claude kar dega.)
4. Live site par: Settings > Permalinks > Save (ek baar). Settings > Reorder Desk site mein emails bharo.
5. Emails ke liye plugin **WP Mail SMTP** (hosting ka mailbox ya Resend), taaki form entries email par aayein.
6. Google Search Console mein site add karo aur sitemap do: `https://<domain>/sitemap_index.xml`.
7. Chaaho to App Store listing mein privacy/support link website ke `/privacy-policy/` aur `/help/` par kar sakte ho.

## 8. Zaroori: naam ka conflict

15 Oct 2026 ko check kiya: **reorderdesk.com** pehle se registered hai aur us par
"Reorder Desk — Outsourced demand planning" chal raha hai: Shopify inventory brands ko weekly PO-ready reorder plans.
Bilkul isi kaam mein, isi naam se. Launch se pehle faisla karo:

- **Naam badlo** (safe): naya naam lo jiska .com / App Store / Google teeno free hon. Website aur app dono mein naam
  badalna Claude ke liye ek-do ghante ka kaam hai.
- **Yahi naam rakho:** trademark aur customers ke confuse hone ka risk rehta hai, aur .com nahi milega
  (reorderdesk.app / getreorderdesk.com abhi free dikh rahe the).

## 9. Abhi tumhe check karna hai

- Pro ki price $29 aur 14 din ka trial pakka hai? (website par yahi likha hai)
- Contact page par "1 working day mein reply" ka vaada theek hai?
- Privacy policy: early-access emails "app live hone ke 12 mahine mein delete" ka vaada theek hai? Company / legal naam
  aur address daalna hai to batao.
- Terms: kis desh ka law lagega (India?) batao, Claude add kar dega.
