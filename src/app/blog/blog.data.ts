// GMATclap blog posts. Newest first.
//
// To add a post: copy one entry (choose its topics from TOPICS below), give it a new unique `slug` (lower-case words joined by
// hyphens; it becomes the address gmatclap.com/blog/<slug>), fill in the fields and write
// the body in simple HTML (<p>, <h2>, <ul>/<li>, <strong>, <a>). Push to main and it goes live.

export interface BlogTopic {
  topic: string; // used in the address: gmatclap.com/blog/topic/<topic>
  name: string;
  group: string;
}

// Blog topics, shown as filters on /blog. Each one has its own page.
// Keep the "{ topic: '" format on one line: the build reads it to pre-render topic pages.
export const TOPICS: BlogTopic[] = [
  { topic: 'gmatprep-quant', name: 'GMATprep Quant', group: 'GMAT & tests' },
  { topic: 'gmatprep-verbal', name: 'GMATprep Verbal', group: 'GMAT & tests' },
  { topic: 'gmatprep-di', name: 'GMATprep DI', group: 'GMAT & tests' },
  { topic: 'executive-assessment', name: 'Executive Assessment (EA)', group: 'GMAT & tests' },
  { topic: 'gmat-waiver', name: 'GMAT waiver', group: 'GMAT & tests' },
  { topic: 'us-universities', name: 'US universities', group: 'Universities' },
  { topic: 'uk-universities', name: 'UK universities', group: 'Universities' },
  { topic: 'europe-universities', name: 'Europe universities', group: 'Universities' },
  { topic: 'canada-universities', name: 'Canada universities', group: 'Universities' },
  { topic: 'asia', name: 'Asia', group: 'Universities' },
  { topic: 'singapore', name: 'Singapore', group: 'Universities' },
  { topic: 'indian-b-schools', name: 'Indian B-schools', group: 'Universities' },
  { topic: 'one-year-mba', name: 'One-year MBA', group: 'Programs' },
  { topic: 'executive-mba', name: 'Executive MBA', group: 'Programs' },
  { topic: 'deferred-mba', name: 'Deferred MBA', group: 'Programs' },
  { topic: 'mim', name: 'MiM', group: 'Programs' },
  { topic: 'phd', name: 'PhD', group: 'Programs' },
  { topic: 'mba-essay', name: 'MBA essay', group: 'Applications' },
  { topic: 'scholarship', name: 'Scholarship', group: 'Applications' },
  { topic: 'admission-consulting-faq', name: 'Admission consulting FAQ', group: 'Applications' },
  { topic: 'success-story', name: 'Success story', group: 'Applications' },
  { topic: 'finance', name: 'Finance', group: 'Careers' },
  { topic: 'consulting', name: 'Consulting', group: 'Careers' },
  { topic: 'technology', name: 'Technology', group: 'Careers' },
];

export const TOPIC_GROUPS = ['GMAT & tests', 'Universities', 'Programs', 'Applications', 'Careers'];

export interface BlogPost {
  slug: string;
  title: string;
  date: string; // YYYY-MM-DD
  author: string;
  topics: string[]; // values from TOPICS[].topic
  summary: string; // one or two sentences for the blog list and Google
  image?: string; // optional, path under assets/images/
  body: string; // HTML
}

export const POSTS: BlogPost[] = [
  {
    slug: 'gmatclap-results-2026',
    title: 'GMATclap results 2026: scores up to 750 and admits to HBS, HEC Paris, NTU and ISB',
    date: '2026-11-26',
    author: 'GMATclap',
    topics: ['success-story'],
    summary:
      'Our students scored between 675 and 750 on the GMAT and won admits to Harvard Business School, HEC Paris, NTU Singapore and ISB.',
    image: 'images/results/shreeya-singh.jpg',
    body: `
<p>Here is how GMATclap students did this year.</p>

<h2>GMAT scores</h2>
<ul>
  <li><strong>Abhishlok – 750.</strong> B.Tech with 6 years in IT.</li>
  <li><strong>Shreeya Singh – 735.</strong> IIT Bombay, 3 years of experience. Admitted to <strong>Harvard Business School</strong>.</li>
  <li><strong>Rahul Nawani – 715.</strong> IIIT Gwalior, 17 years of experience. Admitted to <strong>NTU Singapore</strong>.</li>
  <li><strong>Chano Casal – 710.</strong> B.A.S.C. (Switzerland), 6 years of experience. Admitted to <strong>HEC Paris</strong>.</li>
  <li><strong>Sweta – 700.</strong> B.Tech, 3 years of experience. Admitted to <strong>ISB</strong>.</li>
  <li><strong>Anshul Rajput – 675.</strong></li>
</ul>

<h2>Admits</h2>
<p>Besides the admits above, <strong>Tanu Gupta</strong> was admitted to ISB's YL programme.</p>

<h2>Different backgrounds, one programme</h2>
<p>The list ranges from an engineer with 3 years of experience to a professional with 17, and from Indian to European graduates. Every GMATclap student follows the same programme:</p>
<ul>
  <li>A <strong>diagnostic test</strong> first, so the study plan is built around each student's weak areas.</li>
  <li><strong>Official (OG) questions</strong> solved and discussed in class, more than 3,000 of them.</li>
  <li><strong>Topic tests and full-length mock tests</strong> with detailed analysis.</li>
  <li><strong>Unlimited revision classes</strong> for topics that need another pass.</li>
</ul>

<p>See all our results with photos on the <a href="/results">results page</a>.</p>

<p>Planning your own MBA application? Start with a <a href="/free-profile-evaluation">free profile evaluation</a> and we will tell you which score and which schools make sense for your profile.</p>
`,
  },
  {
    slug: 'gmat-focus-edition-structure-and-scoring',
    title: 'GMAT Focus Edition explained: sections, timing and scoring',
    date: '2026-11-26',
    author: 'GMATclap',
    topics: ['gmatprep-quant', 'gmatprep-verbal', 'gmatprep-di'],
    summary:
      'Three sections, 64 questions, 2 hours 15 minutes and a score from 205 to 805. Here is what is on the GMAT Focus Edition and how it is scored.',
    body: `
<p>The GMAT Focus Edition is the current version of the GMAT. If you are starting your preparation now, this is the exam you will take. Here is the structure at a glance.</p>

<h2>The three sections</h2>
<table>
  <thead><tr><th>Section</th><th>Time</th><th>Questions</th><th>Score range</th></tr></thead>
  <tbody>
    <tr><td>Quantitative Reasoning</td><td>45 min</td><td>21</td><td>60–90</td></tr>
    <tr><td>Verbal Reasoning</td><td>45 min</td><td>23</td><td>60–90</td></tr>
    <tr><td>Data Insights</td><td>45 min</td><td>20</td><td>60–90</td></tr>
  </tbody>
</table>
<p>In total that is <strong>64 questions in 2 hours 15 minutes</strong>, with an overall score from <strong>205 to 805</strong>.</p>

<h2>What each section tests</h2>
<h3>Quantitative Reasoning</h3>
<ul>
  <li>Algebra</li>
  <li>Arithmetic</li>
  <li>Number system</li>
  <li>Statistics</li>
  <li>Permutations, combinations and probability</li>
</ul>
<p>Every question is problem solving. There is no geometry, and no calculator in this section.</p>

<h3>Verbal Reasoning</h3>
<ul>
  <li>Critical Reasoning</li>
  <li>Reading Comprehension</li>
</ul>
<p>Sentence Correction is no longer part of the exam, so verbal preparation is now about reasoning and reading, not grammar rules.</p>

<h3>Data Insights</h3>
<ul>
  <li>Data Sufficiency</li>
  <li>Multi-Source Reasoning</li>
  <li>Table Analysis</li>
  <li>Graphics Interpretation</li>
  <li>Two-Part Analysis</li>
</ul>
<p>Data Insights mixes maths with reading and logic. An on-screen calculator is available in this section.</p>

<h2>Three things that help on test day</h2>
<ul>
  <li><strong>You choose the section order.</strong> Many students start with their strongest section to build confidence; others get their weakest out of the way first. Try both in mock tests.</li>
  <li><strong>You can review and change answers.</strong> You can bookmark questions and change up to three answers per section before time runs out, so a guess under pressure does not have to be final.</li>
  <li><strong>Every section counts equally.</strong> Quant, Verbal and Data Insights all contribute to the 205–805 total, so you cannot ignore Data Insights the way many people once ignored the old Integrated Reasoning section.</li>
</ul>

<h2>How to prepare</h2>
<p>At GMATclap, preparation starts with a diagnostic test so you know where you stand in each section, followed by concept classes, application on official questions and regular tests. You can read more about <a href="/blog/how-we-teach-the-dcat-method">how we teach</a>, or <a href="/enquiry">ask us about the next batch</a>.</p>
`,
  },
  {
    slug: 'how-we-teach-the-dcat-method',
    title: 'How we teach GMAT: the D-C-A-T method',
    date: '2026-11-26',
    author: 'Raaj Tomar',
    topics: ['gmatprep-quant', 'gmatprep-verbal', 'gmatprep-di'],
    summary:
      'Demo and Diagnosis, Concept, Application, Test. The four-step method behind every GMATclap class, and why it works.',
    body: `
<p>Most GMAT students do not fail for lack of effort. They fail because they study everything with the same intensity, solve questions without learning from them, and take their first realistic mock test far too late. Our D-C-A-T method is built to avoid exactly those mistakes.</p>

<h2>D – Demo and Diagnosis</h2>
<p>Before we teach anything, we find out where you stand. A free demo class shows you how we teach, and a diagnostic test shows us your strengths and gaps in Quant, Verbal and Data Insights. Your plan is built from that, not from a one-size-fits-all syllabus.</p>

<h2>C – Concept</h2>
<p>Every topic is taught from first principles in live classes on Zoom, along with the tricks and shortcuts that save time on the real exam. The full course covers about 60 hours of Quant, Verbal and Data Insights classes, plus doubt-clearing sessions.</p>

<h2>A – Application</h2>
<p>A concept only counts once you can use it under time pressure. We share, solve and discuss <strong>more than 3,000 official (OG) questions</strong> in class, so you learn how the GMAT actually tests each idea.</p>

<h2>T – Test</h2>
<p>You test early and often: <strong>130+ topic tests</strong>, unlimited AI-generated practice tests and <strong>6 full-length mock tests</strong>, each with detailed analysis and solutions. Mistakes found in a mock are mistakes you will not make on test day.</p>

<h2>What else comes with it</h2>
<ul>
  <li><strong>Small batches</strong>, so every student gets attention.</li>
  <li><strong>Unlimited revision classes</strong>, at no extra fee.</li>
  <li><strong>Hand-holding until you succeed on the GMAT.</strong> If you want to prepare again, you can attend classes in a later batch for free.</li>
  <li>Complete study material and notes.</li>
</ul>

<h2>Who teaches</h2>
<p>Classes are taught by Raaj Tomar, an engineer from JEC Jabalpur and an MBA from IIM Ahmedabad, who has mentored students for CAT, GMAT, GRE and SAT for over 15 years with IMS, Career Launcher and Leverage Edu.</p>

<p>The best way to see whether this works for you is to try it. <a href="/enquiry">Book a free demo class</a>.</p>
`,
  },
];

export function findPost(slug: string | null): BlogPost | undefined {
  return POSTS.find((p) => p.slug === slug);
}

export function findTopic(topic: string | null): BlogTopic | undefined {
  return TOPICS.find((t) => t.topic === topic);
}

export function topicsOf(post: BlogPost): BlogTopic[] {
  return post.topics.map((t) => findTopic(t)).filter((t): t is BlogTopic => !!t);
}
