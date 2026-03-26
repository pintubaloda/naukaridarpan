/**
 * Naukaridarpan — PYQ Paper Scraper (Playwright)
 * Usage: node scrape_papers.js '{"url":"https://...","category":"upsc"}'
 * Install: npm install && npx playwright install chromium
 */

const args = JSON.parse(process.argv[2] || '{}');

async function run() {
  let chromium, page, browser;
  try {
    ({ chromium } = require('playwright'));
  } catch(e) {
    process.stdout.write(JSON.stringify([]));
    return;
  }

  browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0',
  });
  page = await context.newPage();
  const results = [];

  try {
    await page.goto(args.url || 'about:blank', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.waitForTimeout(1500);

    const pdfLinks = await page.evaluate(() => {
      const links = [];
      document.querySelectorAll('a[href]').forEach(a => {
        const href = a.href || '';
        if (href.toLowerCase().includes('.pdf') || href.includes('question-paper') || href.includes('/qp/')) {
          links.push({ title: (a.textContent || a.title || '').trim().substring(0, 200), pdf_url: href });
        }
      });
      return links;
    });

    const seen = new Set();
    for (const link of pdfLinks) {
      if (!seen.has(link.pdf_url) && link.pdf_url.startsWith('http') && link.title) {
        seen.add(link.pdf_url);
        results.push({ title: link.title, pdf_url: link.pdf_url, category: args.category || 'general', source_url: args.url });
      }
    }
  } catch(err) {
    process.stderr.write('Error: ' + err.message + '\n');
  } finally {
    await browser.close();
  }

  process.stdout.write(JSON.stringify(results));
}

run();
