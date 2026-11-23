const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

function readPngDimensions(filePath) {
  const buf = fs.readFileSync(filePath);
  if (buf.length < 24 || buf.toString('ascii', 1, 4) !== 'PNG') {
    return null;
  }
  return {
    width: buf.readUInt32BE(16),
    height: buf.readUInt32BE(20),
  };
}

async function trimTransparentPng(page, pngBuffer, padding = 4) {
  const base64 = pngBuffer.toString('base64');
  const trimmedDataUrl = await page.evaluate(async (b64, pad) => {
    const img = new Image();
    img.src = 'data:image/png;base64,' + b64;
    await new Promise((resolve, reject) => {
      img.onload = resolve;
      img.onerror = reject;
    });

    const srcCanvas = document.createElement('canvas');
    srcCanvas.width = img.width;
    srcCanvas.height = img.height;
    const srcCtx = srcCanvas.getContext('2d');
    srcCtx.drawImage(img, 0, 0);

    const { data, width, height } = srcCtx.getImageData(0, 0, img.width, img.height);
    let minX = width;
    let minY = height;
    let maxX = -1;
    let maxY = -1;

    for (let y = 0; y < height; y++) {
      const rowOffset = y * width * 4;
      for (let x = 0; x < width; x++) {
        if (data[rowOffset + x * 4 + 3] > 2) {
          if (x < minX) minX = x;
          if (x > maxX) maxX = x;
          if (y < minY) minY = y;
          if (y > maxY) maxY = y;
        }
      }
    }

    if (maxX < minX || maxY < minY) {
      return null;
    }

    const cropW = maxX - minX + 1;
    const cropH = maxY - minY + 1;

    const dstCanvas = document.createElement('canvas');
    dstCanvas.width = cropW + pad * 2;
    dstCanvas.height = cropH + pad * 2;
    const dstCtx = dstCanvas.getContext('2d');
    dstCtx.drawImage(srcCanvas, minX, minY, cropW, cropH, pad, pad, cropW, cropH);

    return dstCanvas.toDataURL('image/png');
  }, base64, padding);

  if (!trimmedDataUrl) return pngBuffer;
  return Buffer.from(trimmedDataUrl.replace(/^data:image\/png;base64,/, ''), 'base64');
}

(async () => {
  let browser;
  try {
    browser = await puppeteer.launch({
      headless: 'new',
      args: ['--no-sandbox', '--disable-setuid-sandbox', '--allow-file-access-from-files'],
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1600, height: 1400, deviceScaleFactor: 4 });

    const kopHtmlFile = path.resolve(__dirname, 'templates/kop/kop-surat.html');
    const indexHtmlFile = path.resolve(__dirname, 'templates/kop/index.html');
    fs.copyFileSync(kopHtmlFile, indexHtmlFile);

    const htmlPath = 'file:///' + kopHtmlFile.replace(/\\/g, '/');
    console.log('Loading:', htmlPath);
    await page.goto(htmlPath, { waitUntil: 'networkidle0' });
    await page.evaluateHandle('document.fonts.ready');
    await new Promise(r => setTimeout(r, 500));

    const outDir = path.resolve(__dirname, 'public/img');
    if (!fs.existsSync(outDir)) {
      fs.mkdirSync(outDir, { recursive: true });
    }

    // 1. Portrait (White & Transparent)
    await page.evaluate(() => window.switchView('portrait'));
    await new Promise(r => setTimeout(r, 300));
    const elPortrait = await page.$('#sheetPortrait .kop-container');
    if (!elPortrait) throw new Error('elPortrait not found!');

    const pWhite = path.join(outDir, 'kop-surat-portrait.png');
    const pTrans = path.join(outDir, 'kop-surat-portrait-transparan.png');

    await page.evaluate(() => {
      document.getElementById('sheetPortrait').style.background = '#ffffff';
      document.querySelector('#sheetPortrait .kop-container').style.background = '#ffffff';
    });
    await elPortrait.screenshot({ path: pWhite, omitBackground: false });

    await page.evaluate(() => {
      document.body.style.backgroundColor = 'transparent';
      document.getElementById('sheetPortrait').style.background = 'transparent';
      document.getElementById('sheetPortrait').style.boxShadow = 'none';
      document.querySelector('#sheetPortrait .kop-container').style.background = 'transparent';
    });
    await elPortrait.screenshot({ path: pTrans, omitBackground: true });

    // Salin ke hires alias
    const pHires = path.join(outDir, 'kop-surat-sman1-gianyar-hires.png');
    fs.copyFileSync(pWhite, pHires);

    // 2. Landscape (White & Transparent)
    await page.evaluate(() => window.switchView('landscape'));
    await new Promise(r => setTimeout(r, 300));
    const elLandscape = await page.$('#sheetLandscape .kop-container');
    if (!elLandscape) throw new Error('elLandscape not found!');

    const lWhite = path.join(outDir, 'kop-surat-landscape.png');
    const lTrans = path.join(outDir, 'kop-surat-landscape-transparan.png');

    await page.evaluate(() => {
      document.getElementById('sheetLandscape').style.background = '#ffffff';
      document.querySelector('#sheetLandscape .kop-container').style.background = '#ffffff';
    });
    await elLandscape.screenshot({ path: lWhite, omitBackground: false });

    await page.evaluate(() => {
      document.body.style.backgroundColor = 'transparent';
      document.getElementById('sheetLandscape').style.background = 'transparent';
      document.getElementById('sheetLandscape').style.boxShadow = 'none';
      document.querySelector('#sheetLandscape .kop-container').style.background = 'transparent';
    });
    await elLandscape.screenshot({ path: lTrans, omitBackground: true });

    // 3. Render 3 baris Aksara Bali PNG Ultra-HD transparan untuk DomPDF
    const aksaraLines = [
      {
        file: 'aksara-bali-1.png',
        text: '\u1B27\u1B42\u1B2B\u1B42\u1B2D\u1B36\u1B26\u1B44\u1B22\u1B04\u1B27\u1B44\u1B2D\u1B40\u1B27\u1B36\u1B26\u1B44\u1B32\u1B36\u1B29\u1B2E\u1B36',
      },
      {
        file: 'aksara-bali-2.png',
        text: '\u1B5E\u1B0F\u1B32\u1B44 \u1B0F\u1B2B\u1B44 \u1B05\u1B5E \u1B26\u1B42\u1B15\u1B42\u1B2D\u1B36 \u1B5E\u1B51\u1B5E \u1B15\u1B44\u1B2C\u1B1C\u1B03',
      },
      {
        file: 'aksara-bali-3.png',
        text: '\u1B1A\u1B2E\u1B26\u1B44\u200C\u1B2D\u1B22\u1B44\u1B26\u1B22\u1B42\u1B15\u1B2E\u1B44\u1B22\u1B38\u1B15\u1B38\u1B15\u1B44\u1B2C\u1B1C\u1B03\u1B5E\u1B58\u1B50\u1B55\u1B51\u1B51\u1B5E\u1B22\u1B3E\u1B2E\u1B3E\u1B27\u1B40\u1B26\u1B44:(\u1B50\u1B53\u1B56\u1B51)\u1B59\u1B54\u1B53\u1B50\u1B53\u1B54',
      },
    ];

    await page.setViewport({ width: 3200, height: 600, deviceScaleFactor: 4 });
    await page.evaluate(() => {
      document.body.innerHTML = '';
      document.body.style.background = 'transparent';
      document.body.style.padding = '40px';
      document.body.style.margin = '0';
      document.body.style.display = 'block';
    });

    for (const item of aksaraLines) {
      await page.evaluate((txt) => {
        const prev = document.getElementById('aksara-render-box');
        if (prev) prev.remove();
        const el = document.createElement('div');
        el.id = 'aksara-render-box';
        el.style.display = 'inline-block';
        el.style.fontFamily = "'Vimala', 'Arial', sans-serif";
        el.style.fontSize = '42px';
        el.style.lineHeight = '2.1';
        el.style.padding = '24px 32px';
        el.style.color = '#000000';
        el.style.whiteSpace = 'nowrap';
        el.style.background = 'transparent';
        el.style.webkitFontSmoothing = 'antialiased';
        el.textContent = txt;
        document.body.appendChild(el);
      }, item.text);
      await page.evaluateHandle('document.fonts.ready');
      await new Promise(r => setTimeout(r, 150));

      const box = await page.$('#aksara-render-box');
      const rawBuf = await box.screenshot({ omitBackground: true });
      const trimmedBuf = await trimTransparentPng(page, rawBuf, 6);
      fs.writeFileSync(path.join(outDir, item.file), trimmedBuf);
    }

    const generatedFiles = [
      'kop-surat-portrait.png',
      'kop-surat-portrait-transparan.png',
      'kop-surat-landscape.png',
      'kop-surat-landscape-transparan.png',
      'kop-surat-sman1-gianyar-hires.png',
      'aksara-bali-1.png',
      'aksara-bali-2.png',
      'aksara-bali-3.png',
    ];

    console.log('Generated PNG files & dimensions:');
    for (const fname of generatedFiles) {
      const fpath = path.join(outDir, fname);
      const dims = readPngDimensions(fpath);
      const sizeKb = (fs.statSync(fpath).size / 1024).toFixed(1);
      console.log(` - ${fname}: ${dims ? dims.width + 'x' + dims.height : 'unknown'} (${sizeKb} KB)`);
    }

    await browser.close();
    console.log('All KOP images generated successfully!');
  } catch (err) {
    console.error('Error generating KOP:', err);
    if (browser) await browser.close();
    process.exit(1);
  }
})();
