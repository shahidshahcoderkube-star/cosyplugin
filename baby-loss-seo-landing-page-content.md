# Baby Loss SEO Landing Page Content Guide

This document contains everything you need to create and publish the new **Baby Loss** SEO Landing Page in WordPress. Every field matches the `SEO Landing Page` template and ACF field structure exactly.

---

## 1. WordPress Basic Page Settings

| Setting | Value to Enter |
| :--- | :--- |
| **Page Title** | `Coping with Baby Loss: Heartfelt Support from Parents Who Understand` |
| **Page Slug (Permalink)** | `coping-with-baby-loss-support` |
| **Full URL** | `https://wppremiumplugins.com/cosychats/coping-with-baby-loss-support/` |
| **Page Template** | `SEO Landing Page` *(Select from Page Attributes)* |
| **Featured Image** | `wp-content/themes/cosychats/assets/images/baby-loss-support-hero.jpg` *(Already saved in theme assets)* |
| **Featured Image Alt Text**| `Empathetic baby loss peer support from parents who understand grief and healing` |

---

## 2. Yoast SEO Plugin Settings

| Field in Yoast SEO | Value to Enter |
| :--- | :--- |
| **Focus Keyphrase** | `baby loss parent support` |
| **SEO Title** | `Coping with Baby Loss: Heartfelt Support from Parents | CosyChats` |
| **Meta Description** | `Navigating the grief of baby loss can feel profoundly lonely. Connect 1-on-1 with parents who have walked through miscarriage, stillbirth, or neonatal loss for gentle, judgment-free support.` |
| **Schema Tab > Page Type** | `Web Page` |
| **Schema Tab > Article Type** | `Article` |

---

## 3. Main WordPress Content Editor (`the_content`)

Copy and paste the following content into the main WordPress editor:

```html
<p>Losing a baby—whether through miscarriage, stillbirth, medical termination, or neonatal death—is a profound, life-altering heartbreak. It carries a specific kind of grief that words often fail to capture, leaving parents feeling untethered in a world that continues moving forward as if nothing happened.</p>

<h2>In the Quiet After Loss: Navigating the Unspoken Grief</h2>

<p>Medical care naturally focuses on physical recovery and test results, but once you leave the hospital or clinic, the silence at home can feel deafening. Friends and family members often struggle to know what to say, sometimes offering well-intentioned clichés that unintentionally deepen the pain. The ache of longing for the future you imagined is real, valid, and deserves gentle space.</p>

<p>Speaking 1-on-1 with a parent who has lived through the devastating milestones of baby loss brings an incomparable sense of comfort. In their presence, there is no need to explain why a nursery door hurts to look at, or why certain dates carry a crushing weight. You are met with pure empathy, deep validation, and the reassuring truth that your baby's memory is cherished.</p>
```

---

## 4. ACF Fields (SEO Landing Page Field Group)

### A. Experience & Parent Details
* **Choose Experience (`choose_experience`):**
  * Select: **`Baby Loss`** *(From cosy_service post object)*
* **Experience User (`experience_user`):**
  * Select a registered parent/provider on your platform (e.g., Sarah / Rachel / Emma)

---

### B. Daily Strategies Section

* **Daily Strategies Heading (`daily_strategies_heading`):**
  `Gentle Ways to Hold Yourself Through Grief and Mourning`

* **Strategy Sub Heading (`strategy_sub_heading`):**
  `There is no correct timeline or handbook for healing after loss. These gentle practices have helped grieving parents find moments of breath and peace:`

* **Daily Strategies List (`daily_strategies_list`) - Repeater (4 Items):**

  1. **Row 1:**
     * **Strategy Title (`strategies_title`):** `Allow Grief to Be Whatever It Is Today`
     * **Strategy Description (`strategies_description`):** `Some days bring numbness, others bring intense tears or sudden anger. All of these emotions are natural expressions of deep love and profound loss.`

  2. **Row 2:**
     * **Strategy Title (`strategies_title`):** `Release the Pressure to "Move On"`
     * **Strategy Description (`strategies_description`):** `Healing does not mean forgetting. It means gradually learning how to carry your baby's love alongside the rest of your life at your own gentle pace.`

  3. **Row 3:**
     * **Strategy Title (`strategies_title`):** `Create Meaningful Acts of Remembrance`
     * **Strategy Description (`strategies_description`):** `Planting a perennial flower, lighting an evening candle, keeping a memory box, or writing private letters can offer grounding comfort and honoring connection.`

  4. **Row 4:**
     * **Strategy Title (`strategies_title`):** `Seek Out Safe, Lived-Experience Spaces`
     * **Strategy Description (`strategies_description`):** `Connecting with people who do not flinch when you mention your baby's name or your pain removes the exhausting burden of pretending to be okay.`

---

### C. What to Expect Card

* **Expect Heading (`expect_heading`):**
  `Key Takeaways & What to Expect in a Chat`

* **Expect List (`expect_list`) - Repeater (3 Items):**
  1. **Point 1 (`add_expect_point`):**
     `A calm, safe, and confidential space where you can speak your baby's name and share your feelings freely.`
  2. **Point 2 (`add_expect_point`):**
     `Zero judgment, unsolicited advice, or hollow clichés—only genuine solidarity from someone who understands.`
  3. **Point 3 (`add_expect_point`):**
     `Flexible booking that respects your emotional energy on any given day.`

---

### D. Lived Experience Section

* **Lived Experience Heading (`lived_experience_heading`):**
  `Why Speaking with a Loss-Experienced Parent Makes a Difference`

* **Lived Experience Description (`lived_experience_description`):**
  ```text
  Grief after baby loss is unique because it is love with nowhere to go. Many parents feel forced to hide their sorrow to make others comfortable, leaving them isolated in their deepest vulnerability. 

  When you talk with a CosyChats parent who has navigated their own baby loss, the armor can finally come down. They understand the sudden waves of grief, the triggers in baby aisles, and the quiet courage it takes just to wake up each morning. Speaking with someone who survived that same darkness and found a way to carry love forward brings genuine hope, compassionate validation, and a reminder that you are never alone.
  ```

---

### E. Peer Support Notice (E-E-A-T Compliance)

* **Support Title (`support_title`):**
  `Peer Support & Lived Experience Notice:`

* **Support Short Description (`support_short_description`):**
  `CosyChats connects parents to share lived experiences, emotional comfort, and peer validation. Our guides share personal perspectives as parents; this content is for supportive guidance and does not replace medical, psychiatric, bereavement counseling, or clinical healthcare advice.`

---

### F. Bottom CTA Box

* **CTA Title (`cta_title`):**
  `You don't have to carry the silence of loss alone`

* **CTA Sub Title (`cta_sub_title`):**
  `Parents on CosyChats have walked through baby loss and chosen to share their time, empathy, and lived experiences. Find someone you'd like to talk with today.`

* **CTA Highlight (`cta_highlight`):**
  `One-to-one gentle conversations based on shared lived experience.`

* **CTA Button (`cta_button`):**
  * **Link URL:** `https://wppremiumplugins.com/cosychats/service-provider/baby-loss/` *(Or your local URL during development)*
  * **Link Title:** `Find a supportive parent`
  * **Target:** Same window (`_self`)

---

## 5. SEO Checklist Before Publishing
- [ ] Permalink is set to `/coping-with-baby-loss-support/`
- [ ] Template `SEO Landing Page` is selected
- [ ] `choose_experience` is set to `Baby Loss`
- [ ] Yoast SEO Focus Keyphrase is filled (`baby loss parent support`)
- [ ] Yoast SEO Schema tab has **Article type** set to **Article**
- [ ] Featured Image is selected from Media Library (`baby-loss-support-hero.jpg`)
