# Scrappy!
### [View demo on Heroku here](https://scrappy-php.herokuapp.com/)


Scrappy is a barebones API for scraping the world-wide-web! Unlike similar projects online, Scrappy is written **entirely** in PHP, and the base code is under 100 lines!

Scrappy has plenty of usage:

1. A Website blocked by your company or ISP? Scrappy can get around that no hassle!

2. Is *cross-origin resource sharing* giving you a hard time? Wrap your URL through Scrappy and forget about it!

3. Get a full copy of another website's HTML directly in your browser, plaintext, or even a JSON Object!


# API Usage

We support **GET** and **POST** HTTP methods! You can pass parameters using **application/x-www-form-urlencoded** or simple [query strings](https://scrappy-php.herokuapp.com/?url=https://example.com) through your address bar.

| Parameter | Type    |Description  |
| --- | --- | --- |
| url       | String  | **(Required)** The webpage we want to scrape. We support all files under 2 megabytes in size.  |
| type      | String  | The response format we want. Choose one depending on what you need: *html* for standard HTML output (the default), *plain* for barebones text-only output, or *json* for a JSON object.   |
| resp      | Boolean | Print out the HTTP response information from cURL instead of the webpage. If the *type* paramater is set to "html" the info will be printed out in an HTML Table element. If the *type* paramater is set to "json" this is not required.   |
| ua        | String  | Specify the [User-Agent](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/User-Agent) that Scrappy will use when fetching.   |
| r         | String  | Specify the [Referer](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referer) that Scrappy will use when fetching.   |
