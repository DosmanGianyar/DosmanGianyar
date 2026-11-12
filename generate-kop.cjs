const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

(async () => {
  try {
    const browser = await puppeteer.launch({ 
      headless: 'new',
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--allow-file-access-from-files']
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1600, height: 1400, deviceScaleFactor: 4 });

    const htmlPath = 'file:///' + path.resolve('C:/Users/dosman/Projects/KOP/kop-surat.html').replace(/\\/g, '/');
    console.log('Loading:', htmlPath);
    await page.goto(htmlPath, { waitUntil: 'networkidle0' });
    await page.evaluateHandle('document.fonts.ready');
    await new Promise(r => setTimeout(r, 500));

    // Ensure target dir exists
    const outDir = path.resolve('C:/Users/dosman/Projects/sims/public/img');
    if (!fs.existsSync(outDir)) {
      fs.mkdirSync(outDir, { recursive: true });
    }

    // 1. Portrait (White & Transparent)
    await page.evaluate(() => window.switchView('portrait'));
    await new Promise(r => setTimeout(r, 300));
    const elPortrait = await page.$('#sheetPortrait .kop-container');
    if (elPortrait) {
      const pWhite = path.join(outDir, 'kop-surat-portrait.png');
      const pTrans = path.join(outDir, 'kop-surat-portrait-transparan.png');
      
      // White Background
      await page.evaluate(() => {
        document.getElementById('sheetPortrait').style.background = '#ffffff';
        document.querySelector('#sheetPortrait .kop-container').style.background = '#ffffff';
      });
      await elPortrait.screenshot({ path: pWhite, omitBackground: false });

      // Transparent Background
      await page.evaluate(() => {
        document.body.style.backgroundColor = 'transparent';
        document.getElementById('sheetPortrait').style.background = 'transparent';
        document.getElementById('sheetPortrait').style.boxShadow = 'none';
        document.querySelector('#sheetPortrait .kop-container').style.background = 'transparent';
      });
      await elPortrait.screenshot({ path: pTrans, omitBackground: true });
      console.log('Saved Portrait to:', pWhite, pTrans);
    } else {
      console.error('elPortrait not found!');
    }

    // 2. Landscape (White & Transparent)
    await page.evaluate(() => window.switchView('landscape'));
    await new Promise(r => setTimeout(r, 300));
    const elLandscape = await page.$('#sheetLandscape .kop-container');
    if (elLandscape) {
      const lWhite = path.join(outDir, 'kop-surat-landscape.png');
      const lTrans = path.join(outDir, 'kop-surat-landscape-transparan.png');

      // White Background
      await page.evaluate(() => {
        document.getElementById('sheetLandscape').style.background = '#ffffff';
        document.querySelector('#sheetLandscape .kop-container').style.background = '#ffffff';
      });
      await elLandscape.screenshot({ path: lWhite, omitBackground: false });

      // Transparent Background
      await page.evaluate(() => {
        document.body.style.backgroundColor = 'transparent';
        document.getElementById('sheetLandscape').style.background = 'transparent';
        document.getElementById('sheetLandscape').style.boxShadow = 'none';
        document.querySelector('#sheetLandscape .kop-container').style.background = 'transparent';
      });
      await elLandscape.screenshot({ path: lTrans, omitBackground: true });
      console.log('Saved Landscape to:', lWhite, lTrans);
    } else {
      console.error('elLandscape not found!');
    }

    await browser.close();
    console.log('All KOP images generated successfully!');
  } catch (err) {
    console.error('Error generating KOP:', err);
    process.exit(1);
  }
})();
