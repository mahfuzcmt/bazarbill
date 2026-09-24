const puppeteer = require('puppeteer-core');
const { execFileSync } = require('child_process');
const ffmpeg = require('ffmpeg-static');
const path = require('path');
const FPS = 30, DUR = parseInt(process.argv[4] || '24', 10);
(async () => {
  const dir = __dirname;
  const browser = await puppeteer.launch({ executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome', headless: 'new', args: ['--font-render-hinting=none'] });
  const page = await browser.newPage();
  await page.setViewport({ width: 1080, height: 1920, deviceScaleFactor: 1 });
  await page.goto('file://' + path.join(dir, process.argv[3] || 'reel.html'), { waitUntil: 'networkidle0' });
  await page.evaluate(() => document.fonts.ready);
  const total = FPS * DUR;
  for (let f = 0; f < total; f++) {
    await page.evaluate((t) => window.seek(t), f / FPS);
    await page.screenshot({ path: path.join(dir, 'frames', String(f).padStart(4, '0') + '.jpg'), type: 'jpeg', quality: 92 });
    if (f % 120 === 0) console.log('frame', f, '/', total);
  }
  await browser.close();
  const out = process.argv[2];
  execFileSync(ffmpeg, ['-y', '-framerate', String(FPS), '-i', path.join(dir, 'frames', '%04d.jpg'), '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-crf', '20', '-preset', 'medium', '-movflags', '+faststart', out], { stdio: 'ignore' });
  console.log('done', out);
})().catch(e => { console.error('ERR', e.message); process.exit(1); });
