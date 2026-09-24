const puppeteer = require('puppeteer');
(async () => {
    const browser = await puppeteer.launch();
    const page = await browser.newPage();
    
    // Listen for console errors
    page.on('console', msg => {
        if (msg.type() === 'error' || msg.type() === 'warning') {
            console.log(msg.type().toUpperCase() + ':', msg.text());
        }
    });
    
    page.on('pageerror', error => {
        console.log('PAGE ERROR:', error.message);
    });

    try {
        await page.goto('http://127.0.0.1:8000/login');
        await page.type('input[type="email"]', 'admin@spi.com'); // guessing
        await page.type('input[type="password"]', 'password');
        await page.click('button[type="submit"]');
        await page.waitForNavigation();
        
        await page.goto('http://127.0.0.1:8000/other-activities/tl/create');
        await page.waitForSelector('.popup-textarea', {timeout: 5000});
        console.log('Found popup-textarea, clicking...');
        await page.click('.popup-textarea');
        await new Promise(r => setTimeout(r, 1000));
        
        const modalVisible = await page.evaluate(() => {
            const modal = document.getElementById('textEditorModal');
            return modal && modal.style.display === 'block';
        });
        console.log('Modal visible:', modalVisible);
    } catch(err) {
        console.log('Script error:', err.message);
    }
    
    await browser.close();
})();
