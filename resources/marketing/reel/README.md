# DueTap reels

Vertical 1080×1920 product reels rendered from HTML with real app screenshots.

Files: `reel.html` (24 s intro), `steps.html` (20 s "how it works"), `render.js` (frame capture + ffmpeg).

Needs Google Chrome, plus `puppeteer-core` and `ffmpeg-static` installed next to this folder (`npm i puppeteer-core ffmpeg-static`).
Copy `public/brand/logo-horizontal-dark.png`, `public/brand/logo-horizontal-light.png`, `public/icons/icon-512.png`
and a `shots/` folder of 390×844 (3×) screenshots (owner-dashboard, owner-invoices, collector-shops, collector-payment, tenant-invoices) beside the HTML, then:

    mkdir -p frames && node render.js ../../../duetap-reel-intro.mp4 reel.html 24
    node render.js ../../../duetap-reel-steps.mp4 steps.html 20

Each page exposes `window.seek(seconds)`; the renderer steps it 30 times per second and stitches the frames with ffmpeg (H.264, yuv420p). The videos are silent: add music in the Facebook or Instagram editor.
