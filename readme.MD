# WP Resonate
*Unofficial Resonate sermon integration plugin for WordPress.*

This plugin integrates Resonate sermons with a WordPress site.

It includes a [WPResonateContent] shortcode to embed your church's sermons from the Resonate site, and also an optional widget that allows filtering this page by sermon series.

# Installation

1. Upload & activate.

2. Create a new page for "Sermons" if you don't already have one.

3. Edit the "Sermons" page to include the shortcode `[WPResonateContent]`

4. In the WP Resonate admin page, enter your church ID.

5. To also use the optional widget (described below), make sure you set the **Sermon page** to point to the "Sermons" page.

[![https://gyazo.com/405d8112ec6c85ae0d3dff379764e6bc](https://i.gyazo.com/405d8112ec6c85ae0d3dff379764e6bc.png)](https://gyazo.com/405d8112ec6c85ae0d3dff379764e6bc)

# Optional widget

If you wish - and your theme supports it - you can include a widget for visitors to filter the sermon listing by the preaching topic/series. The Resonate website is periodically checked to look for new (or changed) topic listings.

Use the "Force update now" link in the WP Resonate admin page to manually trigger a listings update.

[![https://gyazo.com/b1347ca94b96df6e81237ed065d681f8](https://i.gyazo.com/b1347ca94b96df6e81237ed065d681f8.png)](https://gyazo.com/b1347ca94b96df6e81237ed065d681f8)
