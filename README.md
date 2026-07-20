# 🦚 કૃષ્ણા ટૂલ્સ (KRISHNA TOOLS)

**AK Computer, દ્વારકા (Dwarka)** દ્વારા બનાવેલ — ૧૨૦+ ઓનલાઇન ટૂલ્સનું એક જ પ્લેટફોર્મ.

> આ README દુકાનના માલિક (non-technical shop owner) માટે **cPanel shared hosting** પર step-by-step ઇન્સ્ટોલેશન સમજાવે છે. મુખ્ય ભાષા ગુજરાતી છે, જ્યાં જરૂરી હોય ત્યાં English પણ આપ્યું છે.

---

## 📖 પરિચય (Introduction)

**કૃષ્ણા ટૂલ્સ** એ એક વેબ એપ્લિકેશન છે જેમાં **૧૨૦+ ટૂલ્સ** (PDF, ઇમેજ, ટેક્સ્ટ, કન્વર્ટર, કેલ્ક્યુલેટર, વગેરે) એક જ જગ્યાએ મળે છે.

- 🏢 **બનાવનાર (Made by):** AK Computer, દ્વારકા (Dwarka)
- 🦚 **થીમ (Theme):** ભગવાન શ્રીકૃષ્ણ (Lord Krishna) — મોરપીંછ, જન્માષ્ટમી ફેસ્ટિવલ થીમ, અને દૈનિક ભગવદ્ ગીતા શ્લોક.
- 🔐 **લોગિન:** સબસ્ક્રિપ્શન આધારિત (subscription login) — યુઝર રજિસ્ટર કરે, પ્લાન લે અને ટૂલ્સ વાપરે.
- 💬 **WhatsApp integration:** `bulk.akdwk.in` પેનલ સાથે જોડાણ — OTP, રિમાઇન્ડર અને નોટિફિકેશન WhatsApp પર મોકલાય.
- 💳 **પેમેન્ટ:** Razorpay (optional) દ્વારા સબસ્ક્રિપ્શન પ્લાન ખરીદી.

---

## ✅ જરૂરિયાત (Requirements)

| વસ્તુ | જરૂરિયાત |
|-------|-----------|
| PHP | **8.1 અથવા વધુ** (extensions: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `curl`, `zip`, `fileinfo`) |
| ડેટાબેઝ | **MySQL 8** અથવા **MariaDB 10.4+** |
| હોસ્ટિંગ | કોઈ પણ **cPanel shared hosting** |
| Composer / Node | **જરૂર નથી** — બધું pure PHP માં લખેલું છે ✅ |

> 💡 **નોંધ:** તમારે terminal/SSH ની જરૂર નથી. બધું cPanel ના ગ્રાફિકલ ટૂલ્સથી થઈ જશે.

---

## 🚀 ઇન્સ્ટોલેશન (Installation) — Step by Step

### Step 1 — 🌐 સબડોમેન સેટઅપ (Subdomain Setup)

1. cPanel માં લોગિન કરો.
2. **Domains → Subdomains** પર જાઓ.
3. નવો સબડોમેન બનાવો:
   - **Subdomain:** `tools`
   - **Domain:** `akdwk.in`
   - **Document Root:** આપોઆપ ભરાઈ જશે, દા.ત. `/home/USER/tools.akdwk.in` અથવા `public_html/tools`
4. **Create** પર ક્લિક કરો.

> `USER` = તમારું cPanel username. તેને યાદ રાખો — આગળ CRON અને paths માં જોઈશે.

---

### Step 2 — 📁 ફાઇલ અપલોડ (Upload Files)

1. cPanel માં **File Manager** ખોલો.
2. ઉપર બનાવેલા document root (`tools.akdwk.in`) ફોલ્ડરમાં જાઓ.
3. પ્રોજેક્ટની **ZIP ફાઇલ** અહીં **Upload** કરો.
4. ZIP પર **right-click → Extract** કરો.
5. Extract થયા પછી ZIP ફાઇલ ડિલીટ કરી શકો છો.

> વૈકલ્પિક રીતે તમે બધી ફાઇલો સીધી (without ZIP) પણ અપલોડ કરી શકો છો, પણ ZIP + Extract સૌથી ઝડપી છે.

---

### Step 3 — 🗄️ ડેટાબેઝ બનાવો (Create Database)

cPanel → **MySQL Databases** પર જાઓ:

1. **નવો ડેટાબેઝ બનાવો:** દા.ત. `akdwk_krishnatools`
2. **નવો યુઝર બનાવો:** દા.ત. `akdwk_ktuser` + એક **મજબૂત પાસવર્ડ (strong password)**
3. **Add User To Database** સેક્શનમાં:
   - યુઝરને ડેટાબેઝ સાથે જોડો
   - **ALL PRIVILEGES** ✅ ટિક કરો → **Make Changes**

> 📝 **આ ત્રણ વસ્તુ ક્યાંક લખી રાખો** (ઇન્સ્ટોલરમાં જોઈશે):
> - Database name: `akdwk_krishnatools`
> - Username: `akdwk_ktuser`
> - Password: `********`

---

### Step 4 — ⚙️ ઇન્સ્ટોલર ચલાવો (Run the Installer)

બ્રાઉઝરમાં ખોલો:

```
https://tools.akdwk.in/install/
```

ઇન્સ્ટોલર **૫ સ્ટેપ** માં ચાલશે:

1. **System Check** — PHP વર્ઝન, extensions અને ફોલ્ડર permissions તપાસે.
2. **Database** — ઉપરની DB details ભરો (host સામાન્ય રીતે `localhost`).
3. **Site + Admin** — સાઇટનું નામ, URL, અને **એડમિન ઇમેઇલ + પાસવર્ડ** સેટ કરો.
4. **Integrations** — WhatsApp / Razorpay / SMTP details (optional — પછી પણ ભરી શકાય).
5. **Install** — બધું ઇન્સ્ટોલ કરે.

#### 🔴 લાલ (RED) અને 🟡 પીળા (YELLOW) આઇટમ સમજો

- **🔴 લાલ = અવશ્ય ઠીક કરવું પડશે.** સામાન્ય રીતે આ **ફોલ્ડર permissions** હોય છે. નીચેના ફોલ્ડરને **755** permission આપો (File Manager → ફોલ્ડર પર right-click → **Change Permissions → 755**):
  - `config`
  - `uploads`
  - `uploads/temp`
  - `assets/img/logo`
  - `logs`
- **🟡 પીળા = માત્ર ચેતવણી (warning).** ઇન્સ્ટોલ ચાલુ રહેશે, પણ સંબંધિત ફીચર કદાચ ન ચાલે (દા.ત. કોઈ optional extension).

#### ✅ ઇન્સ્ટોલર આપોઆપ શું કરે છે?

- બધા **ડેટાબેઝ ટેબલ** બનાવે.
- **૧૨૦ ટૂલ્સ + કેટેગરી + પ્લાન + WhatsApp ટેમ્પ્લેટ + બ્લોગ** seed કરે.
- `config/config.php` ફાઇલ લખે (તમારી credentials સાથે).
- `.htaccess` લખે.
- `config/install.lock` બનાવે (જેથી ઇન્સ્ટોલર ફરી ન ચાલે).

---

### Step 5 — 🔒 `/install/` ડિલીટ કરો (Security)

ઇન્સ્ટોલ પૂરું થયા પછી **સુરક્ષા માટે** `install` ફોલ્ડર કાઢી નાખો:

- ઇન્સ્ટોલરની છેલ્લી સ્ક્રીન પર **Auto-Delete** બટન હોય તો તે વાપરો, **અથવા**
- File Manager માં જઈને `install` ફોલ્ડર જાતે ડિલીટ કરો.

> ⚠️ `install/repair.php` પછી પણ કામનું છે (Step: રિપેર જુઓ), પણ આખું `install/` ફોલ્ડર public રહે તો સુરક્ષા જોખમ છે. જો repair ભવિષ્યમાં જોઈએ તો ફોલ્ડરનું બેકઅપ રાખો.

---

### Step 6 — ⏰ CRON સેટઅપ (Cron Jobs)

cPanel → **Cron Jobs** પર જાઓ અને નીચેની **ત્રણ** cron lines બરાબર ઉમેરો:

```cron
*/30 * * * * php /home/USER/tools.akdwk.in/cron/cleanup.php
0 9 * * * php /home/USER/tools.akdwk.in/cron/expiry_reminder.php
0 10 * * * php /home/USER/tools.akdwk.in/cron/amc_reminder.php
```

- `cleanup.php` — દર **૩૦ મિનિટે** temp ફાઇલો સાફ કરે.
- `expiry_reminder.php` — રોજ **સવારે ૯ વાગ્યે** પ્લાન expiry રિમાઇન્ડર મોકલે.
- `amc_reminder.php` — રોજ **સવારે ૧૦ વાગ્યે** AMC રિમાઇન્ડર મોકલે.

> 🔧 **અગત્યનું:** `/home/USER/tools.akdwk.in/` ને તમારા **અસલી path** થી બદલો.
> તમારો path cPanel → File Manager ના address bar માં દેખાય છે (દા.ત. `/home/akdwk/tools.akdwk.in`).
> કેટલાક હોસ્ટ પર `php` ને બદલે full path (દા.ત. `/usr/local/bin/php` અથવા `/usr/bin/php8.1`) લખવો પડે — હોસ્ટને પૂછો.

---

### Step 7 — 💬 WhatsApp Webhook

`bulk.akdwk.in` પેનલમાં જાઓ અને **outbound/inbound webhook** ફીલ્ડમાં આ URL પેસ્ટ કરો:

```
https://tools.akdwk.in/api/whatsapp_webhook.php
```

> ✅ ઇન્સ્ટોલરમાં **API key / session ID પહેલેથી ભરેલા (pre-filled)** હોય છે — જરૂર પડે ત્યાં ચકાસી લો.
> WhatsApp webhook માટે સાઇટ **HTTPS** પર હોવી ફરજિયાત છે.

---

### Step 8 — 💳 Razorpay (Optional)

ઓનલાઇન પેમેન્ટ જોઈએ તો:

1. **Key ID** અને **Key Secret** ઇન્સ્ટોલરમાં અથવા **Admin → Settings** માં ઉમેરો.
2. **Webhook Secret** સેટ કરો — settings key: `rzp_webhook_secret`.
3. **Razorpay Dashboard** માં webhook URL ઉમેરો:

   ```
   https://tools.akdwk.in/api/razorpay_webhook.php
   ```

   Events: **`payment.captured`** અને **`order.paid`** સિલેક્ટ કરો.

---

### Step 9 — 📧 SMTP Email (Optional)

ઇમેઇલ મોકલવા (પાસવર્ડ રીસેટ, રિમાઇન્ડર વગેરે) માટે:

- ઇન્સ્ટોલર અથવા **Admin → Settings** માં **SMTP** details ભરો:
  - `SMTP_HOST`, `SMTP_PORT` (587 = STARTTLS, 465 = SSL), `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM_NAME`
- ઇમેઇલ મોકલવાનું કામ **`includes/mailer.php`** સંભાળે છે (કોઈ Composer/library વગર, pure PHP SMTP).

> 💡 Advanced admins ઇચ્છે તો official **PHPMailer** single-file build `includes/` માં મૂકીને include બદલી શકે છે.

---

## 🛠️ એડમિન પેનલ (Admin Panel)

```
https://tools.akdwk.in/admin/
```

- Step 4 માં સેટ કરેલા **એડમિન ઇમેઇલ + પાસવર્ડ** થી લોગિન કરો.
- અહીંથી ટૂલ્સ, પ્લાન, યુઝર, પેમેન્ટ, સેટિંગ્સ અને બ્લોગ મેનેજ કરો.

---

## 🔁 રિપેર / રી-સીડ (Repair / Re-seed)

જો ટૂલ્સ / કેટેગરી / પ્લાન / ટેમ્પ્લેટ ડેટા બગડે અથવા અપડેટ કરવો હોય:

```
https://tools.akdwk.in/install/repair.php
```

- આ **ટૂલ્સ, કેટેગરી, પ્લાન અને ટેમ્પ્લેટ ફરી seed કરે છે.**
- ✅ **યુઝર અને પેમેન્ટ ડેટા ડિલીટ થતો નથી** (safe re-seed).
- 🔐 આ ચલાવવા **એડમિન લોગિન જરૂરી** છે.

---

## 🧱 ટેક સ્ટેક (Tech Stack)

| લેયર | ટેકનોલોજી |
|------|-----------|
| Backend | **PHP 8.1** (pure, no framework, no Composer) |
| Database | **MySQL / MariaDB** (PDO, table prefix `kt_`) |
| Frontend | HTML + CSS + Vanilla JavaScript |
| Email | Custom SMTP client (`includes/mailer.php`) |
| Payments | Razorpay (optional) |
| Messaging | WhatsApp via `bulk.akdwk.in` API |

### 📂 ફોલ્ડર માળખું (Folder Structure — brief)

```
tools.akdwk.in/
├── admin/          → એડમિન પેનલ
├── api/            → webhook & AJAX endpoints (whatsapp, razorpay, convert…)
├── assets/         → CSS, JS, images, logo
├── blog/           → બ્લોગ પેજ
├── config/         → config.php (installer લખે) + install.lock
├── cron/           → cleanup / expiry_reminder / amc_reminder
├── includes/       → functions.php, db.php, mailer.php, tools_registry.php
├── install/        → ઇન્સ્ટોલ wizard + repair.php
├── logs/           → error logs
├── sql/            → ડેટાબેઝ schema / seed
├── tools/          → ૧૨૦+ ટૂલ્સ
└── uploads/        → યુઝર ફાઇલો (uploads/temp = કામચલાઉ)
```

---

## 🔐 સુરક્ષા નોંધ (Security Notes)

- ✅ `config/install.lock` ફાઇલ **રાખો** — તેને ડિલીટ ન કરો (નહીંતર ઇન્સ્ટોલર ફરી ખૂલી શકે).
- ❌ `config/config.php` **કોઈ સાથે શેર ન કરો** — તેમાં ડેટાબેઝ પાસવર્ડ છે.
- 🔒 સાઇટ **HTTPS** પર જ ચલાવો — WhatsApp webhook માટે ફરજિયાત.
- 🗑️ ઇન્સ્ટોલ પછી `install/` ફોલ્ડર ડિલીટ કરો (Step 5).

---

## 🎞️ વૈકલ્પિક સર્વર બાઇનરી (Optional Server Binaries)

જો હોસ્ટિંગ પર આ binaries ઇન્સ્ટોલ હોય તો વધારાના high-quality conversions ચાલુ થાય છે:

| Binary | શેના માટે |
|--------|-----------|
| **Ghostscript** | PDF compress / advanced PDF ops |
| **LibreOffice** | Office → PDF (Word/Excel/PPT conversion) |
| **ImageMagick** | advanced image conversion |
| **FFmpeg** | video / audio conversion |

> 🟢 આ **જરૂરી નથી.** આ binaries ન હોય તો સાઇટ **browser-based fallbacks** વાપરે છે — ટૂલ્સ ચાલુ જ રહેશે, ફક્ત કેટલાક conversions થોડા મર્યાદિત હશે. Shared hosting પર આ ઉમેરવા હોસ્ટને પૂછો.

---

## 📞 સંપર્ક (Contact)

**AK Computer, દ્વારકા (Dwarka)**
તમારી દુકાન — તમારા ૧૨૦+ ટૂલ્સ, એક જ જગ્યાએ.

---

### 🦚 હરે કૃષ્ણ | Hare Krishna 🦚
