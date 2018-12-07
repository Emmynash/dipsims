// import faker from "faker";
// import puppeteer from "puppeteer";

// const APP = "http://dipsims-aef.c9users.io/#/teachers";

// const lead = {
//   Fullname: faker.name.firstName(),
//   email: faker.internet.email(),
//   username: faker.internet.userName(),
//   password: faker.internet.password()
// };

// const mine = {
//     username: 'daaef',
//     password: 'badmus86'
// };

// let page;
// let browser;
// const width = 1920;
// const height = 1080;


// beforeAll(async () => {
//     browser = await puppeteer.launch({
//         headless: false,
//         slowMo: 80,
//         args: [`--window-size=${width}, ${height}`]
//     });
//     page = await browser.newPage();
//     await page.setViewport({ width, height});
// });

// describe("Add Teacher Form", () => {
//     test("lead can add a teacher to table", async () => {
//         if (document.querySelector('.solid.fat.info.button')) {
//             await page.click('.solid.fat.info.button');
//         }else if(document.querySelector('.login-box-body')){
//             await page.waitForSelector('.login-box-body form');
//             await page.click('input[name="email"]');
//             await page.type('input[name="email"]', mine.username);
//             await page.click('input[name="password"]')
//             await page.type('input[name="password"]', mine.password);
//             await page.click('input[name="remember_me"]');
//             await page.click('button[type="submit"]');
//         }else if(window.location == 'http://dipsims-aef.c9users.io/');
//     await page.waitForSelector("#content");
//     await page.click('input[name="fullName"]');
//     await page.type('input[name="fullName"]', lead.Fullname);
//     await page.click('input[name="username"]');
//     await page.type('input[name="username"]', lead.username);
//     await page.click('input[name="email"]');commit
//     await page.type('input[name="email"]', lead.email);
//     await page.click('input[name="password"]');
//     await page.type('input[name="password"]', lead.password);
//     await page.click('input[type="submit"]');
//     await page.waitForSelector("#addTeach");
//         }, 16000);
// })

// afterAll(() => {
//     browser.close();
// });