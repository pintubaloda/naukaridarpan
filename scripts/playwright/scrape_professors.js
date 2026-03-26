/**
 * Naukaridarpan — Professor Lead Scraper (Playwright)
 * Usage: node scrape_professors.js '{"limit":100}'
 * Scrapes public college faculty pages and education-oriented public profiles.
 */

const args = JSON.parse(process.argv[2] || '{}');
const limit = args.limit || 100;

const COLLEGE_SOURCES = [
  'https://www.du.ac.in/du/index.php?page=faculty',
  'https://www.jnu.ac.in/main/academics/faculty',
  'https://www.iitd.ac.in/content/faculty',
];

async function run() {
  let chromium;
  try { ({ chromium } = require('playwright')); }
  catch(e) { process.stdout.write(JSON.stringify([])); return; }

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ userAgent: 'Mozilla/5.0 Chrome/120.0' });
  const results = [];

  for (const sourceUrl of COLLEGE_SOURCES) {
    if (results.length >= limit) break;
    const page = await context.newPage();
    try {
      await page.goto(sourceUrl, { waitUntil: 'domcontentloaded', timeout: 20000 });
      await page.waitForTimeout(1000);

      const faculty = await page.evaluate(() => {
        const leads = [];
        // Generic selectors that work across many college sites
        document.querySelectorAll('[class*="faculty"],[class*="staff"],[class*="professor"],[class*="teacher"]').forEach(el => {
          const text = el.innerText || '';
          const emailMatch = text.match(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/);
          const nameEl = el.querySelector('h2,h3,h4,.name,[class*="name"]');
          if (emailMatch || nameEl) {
            leads.push({
              name: nameEl ? nameEl.textContent.trim() : '',
              email: emailMatch ? emailMatch[0] : null,
              institution: document.title,
              platform: 'College Website',
              profile_url: window.location.href,
            });
          }
        });
        return leads.slice(0, 30);
      });

      results.push(...faculty);
    } catch(e) {
      process.stderr.write('Error scraping ' + sourceUrl + ': ' + e.message + '\n');
    } finally {
      await page.close();
    }
  }

  await browser.close();
  process.stdout.write(JSON.stringify(results.slice(0, limit)));
}

run();
