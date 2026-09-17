# IVF SEO Landing Page Content Guide

This document contains everything you need to create and publish the new **IVF (In Vitro Fertilization)** SEO Landing Page in WordPress. Every field matches the `SEO Landing Page` template and ACF field structure exactly.

---

## 1. WordPress Basic Page Settings

| Setting | Value to Enter |
| :--- | :--- |
| **Page Title** | `Navigating IVF: Real Support from Parents Who Have Walked the Path` |
| **Page Slug (Permalink)** | `navigating-ivf-support-from-parents` |
| **Full URL** | `https://wppremiumplugins.com/cosychats/navigating-ivf-support-from-parents/` |
| **Page Template** | `SEO Landing Page` *(Select from Page Attributes)* |
| **Featured Image** | `wp-content/themes/cosychats/assets/images/ivf-support-hero.jpg` *(Already saved in your theme assets)* |
| **Featured Image Alt Text**| `Empathetic IVF peer support from parents who have been through fertility treatment` |




---

## 2. Yoast SEO Plugin Settings

| Field in Yoast SEO | Value to Enter |
| :--- | :--- |
| **Focus Keyphrase** | `IVF parent support` |
| **SEO Title** | `Navigating IVF: Real Support from Parents | CosyChats` |
| **Meta Description** | `Undergoing IVF is an emotional rollercoaster. Connect 1-on-1 with parents who have lived through the rounds, two-week waits, and heartbreaks to find genuine hope and calm guidance.` |
| **Schema Tab > Page Type** | `Web Page` |
| **Schema Tab > Article Type** | `Article` |

---

## 3. Main WordPress Content Editor (`the_content`)

Copy and paste the following content into the main WordPress editor:

```html
<p>The journey through IVF and fertility treatment is one of the most physically, emotionally, and mentally demanding experiences a person or couple can endure. Behind every clinic visit, injection schedule, and waiting room moment lies a deeply personal story of hope, resilience, and vulnerability.</p>

<h2>Beyond the Clinic: The Emotional Reality of IVF</h2>

<p>Medical teams provide vital clinical protocols, hormone dosages, and laboratory updates, but they rarely have time to address the quiet emotional weight that fills the days between appointments. The anxiety of the "two-week wait," the strain on relationships, and the exhaustion of trying to balance a career with constant blood tests often leave individuals feeling profoundly isolated.</p>

<p>Speaking 1-on-1 with a parent who has lived through the exact ups and downs of fertility cycles offers something clinical brochures cannot: heartfelt validation, practical coping wisdom, and a compassionate reminder that you do not have to carry this alone.</p>
```

---

## 4. ACF Fields (SEO Landing Page Field Group)

### A. Experience & Parent Details
* **Choose Experience (`choose_experience`):**
  * Select: **`IVF`** *(From cosy_service post object)*
* **Experience User (`experience_user`):**
  * Select a registered parent/provider on your platform (e.g., Emma / Sarah / David)

---

### B. Daily Strategies Section

* **Daily Strategies Heading (`daily_strategies_heading`):**
  `Daily Strategies to Guard Your Well-being During Treatment`

* **Strategy Sub Heading (`strategy_sub_heading`):**
  `While every fertility path is deeply personal, these daily habits have helped many navigate the treatment cycles with greater calm and mental clarity:`

* **Daily Strategies List (`daily_strategies_list`) - Repeater (4 Items):**

  1. **Row 1:**
     * **Strategy Title (`strategies_title`):** `Protect Your Emotional Energy`
     * **Strategy Description (`strategies_description`):** `Set gentle boundaries around who you share cycle updates with; it is okay to keep details private to avoid fielding well-meaning but draining questions.`

  2. **Row 2:**
     * **Strategy Title (`strategies_title`):** `Break the Cycle into Micro-Milestones`
     * **Strategy Description (`strategies_description`):** `Focus only on the single injection, scan, or appointment in front of you today rather than carrying the weight of the entire protocol at once.`

  3. **Row 3:**
     * **Strategy Title (`strategies_title`):** `Plan Two-Week Wait Distractions`
     * **Strategy Description (`strategies_description`):** `Fill the post-transfer waiting period with gentle, immersive activities—audiobooks, creative projects, or calm walks—to reduce obsessive symptom-checking.`

  4. **Row 4:**
     * **Strategy Title (`strategies_title`):** `Nurture Partner Connection Beyond IVF`
     * **Strategy Description (`strategies_description`):** `Designate deliberate "treatment-free" conversations and date nights where medical topics, test results, and next steps are completely off the table.`

---

### C. What to Expect Card

* **Expect Heading (`expect_heading`):**
  `Key Takeaways & What to Expect in a Chat`

* **Expect List (`expect_list`) - Repeater (3 Items):**
  1. **Point 1 (`add_expect_point`):**
     `Heartfelt, confidential 1-on-1 conversations with parents who truly understand fertility challenges.`
  2. **Point 2 (`add_expect_point`):**
     `A compassionate, judgment-free space to voice fears, grief, and hopes openly.`
  3. **Point 3 (`add_expect_point`):**
     `Practical insights into managing day-to-day stress, clinic appointments, and emotional fatigue.`

---

### D. Lived Experience Section

* **Lived Experience Heading (`lived_experience_heading`):**
  `Why Speaking with an IVF-Experienced Parent Matters`

* **Lived Experience Description (`lived_experience_description`):**
  ```text
  Friends and family often want to support you, but unless they have set an alarm for a midnight hormone injection, waited by the phone for embryology lab reports, or dealt with a failed transfer, it is difficult for them to truly grasp what you are carrying. 

  CosyChats connects you with parents who have stood in those exact shoes. There is immense comfort in speaking to someone who remembers the heavy waiting rooms, understands the language of follicles and betas, and can listen without offering unsolicited medical advice or toxic positivity. You receive real empathy, authentic solidarity, and reassurance from someone who made it through.
  ```

---

### E. Peer Support Notice (E-E-A-T Compliance)

* **Support Title (`support_title`):**
  `Peer Support & Lived Experience Notice:`

* **Support Short Description (`support_short_description`):**
  `CosyChats connects individuals and couples to share lived experiences, emotional reassurance, and practical coping tools. Our guides share personal perspectives as parents; this content is for supportive guidance and does not replace medical, reproductive, endocrinological, or clinical healthcare advice.`

---

### F. Bottom CTA Box

* **CTA Title (`cta_title`):**
  `Your fertility journey doesn't have to be walked alone`

* **CTA Sub Title (`cta_sub_title`):**
  `Parents on CosyChats have navigated IVF and chosen to share their lived experiences. Find someone you'd like to talk to today.`

* **CTA Highlight (`cta_highlight`):**
  `One-to-one confidential conversations based on shared lived experience.`

* **CTA Button (`cta_button`):**
  * **Link URL:** `https://wppremiumplugins.com/cosychats/service-provider/ivf/` *(Or your local URL during development)*
  * **Link Title:** `Find an IVF parent`
  * **Target:** Same window (`_self`)

---

## 5. SEO Checklist Before Publishing
- [ ] Permalink is set to `/navigating-ivf-support-from-parents/`
- [ ] Template `SEO Landing Page` is selected
- [ ] `choose_experience` is set to `IVF`
- [ ] Yoast SEO Focus Keyphrase is filled (`IVF parent support`)
- [ ] Yoast SEO Schema tab has **Article type** set to **Article**
- [ ] Featured Image is uploaded with descriptive alt text
