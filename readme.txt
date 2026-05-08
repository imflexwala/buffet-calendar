=== Buffet Calendar and Meal Schedule - Opening Hours with Pricing ===
Contributors: mustafaflexwala
Tags: opening hours, buffet, meal schedule, restaurant hours, hotel calendar
Requires at least: 5.6
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Color-coded monthly calendar to publish daily opening hours, buffet times, meal-service windows, and pricing for hotels, restaurants, and cafés.

== Description ==

**Buffet Calendar and Meal Schedule** is the easiest way to publish daily opening hours, buffet times, meal-service windows, and pricing on a WordPress site. Whether you run a hotel breakfast buffet, a restaurant with changing weekly hours, a café with coffee & cake afternoons, or a seasonal resort, this plugin shows guests at a glance what's open today and what's coming this month.

Every day on the calendar is color-coded by a label category you control — Breakfast, Lunch Buffet, Dinner Buffet, Brunch, Coffee & Cake, Closed, Happy Hour, Holiday, or anything else you need. A legend below the calendar explains what each color means and what it costs, so visitors stop calling to ask "are you serving lunch today?"

= Who is it for? =

* **Hotels** publishing breakfast, lunch, and dinner buffet schedules
* **Restaurants** with different opening hours each weekday
* **Cafés and tea houses** showing coffee & cake afternoons
* **Spa, wellness, and resort venues** with daily program availability
* **Seasonal businesses** — beach clubs, ski lodges, vineyards, beer gardens, lake restaurants — with hours that change through the year
* **Wedding and event venues** marking open vs. booked dates
* **Co-working spaces, museums, and tourist sites** with weekly opening hours
* Any business that wants a clean monthly view of "what's open and when"

= Why not a regular events plugin? =

Most WordPress calendar and event plugins are built for bookings, classes, ticketing, or one-off events. They're complex and overkill when all you need is to display **what's available each day**. Buffet Calendar focuses on this single job — color-coded daily availability with a legend — and does it cleanly, without bookings, payments, or guest data.

= Key features =

* **Unlimited custom label categories** — add as many label rows as you need (Breakfast, Brunch, Lunch Buffet, Dinner Buffet, Coffee & Cake, Closed, Holiday, Happy Hour, Pool Bar, Spa, etc.). The 6 defaults work for most hotels and restaurants out of the box.
* **WordPress color picker per label** — choose any hex color for each label. Calendar cells and the legend swatch update together. Default palette of yellow, green, orange, blue, beige, and red is pre-loaded.
* **Enable / disable any label** — toggle labels off temporarily without deleting them. Perfect for seasonal categories (e.g. a summer "Pool Bar" you want to hide in winter). Days already tagged with a disabled label keep their color until you reassign them.
* **Add and remove labels** — fully dynamic Settings page. Each label has its own row with a textarea for the displayed text, a color picker, an enabled checkbox, and a remove button.
* **12 months at a glance** — first 3 months shown by default; remaining months reveal with a "Show more" button so visitors can plan ahead.
* **Translucent color cells** — each day cell is tinted so the date number and weekday/weekend background still read cleanly underneath.
* **Responsive 3-column layout** — three months side-by-side on desktop, two on tablet, single column on mobile. Works inside any theme.
* **Shortcode-driven** — drop `[buffet_calendar_frontend]` into any page, post, classic editor, block editor, or widget area. Compatible with Elementor, Beaver Builder, Divi, and other page builders.
* **Translation-ready** — every visible string is wrapped for WordPress i18n. A `.pot` template ships in `/languages/`. Frontend month and day names follow your site's WordPress language automatically.
* **English-only admin UI** — admin pages always display in English regardless of site locale, so multi-language teams stay on the same page.
* **No external services** — no tracking, no analytics, no third-party API calls, no telemetry. Everything stays on your server.
* **Privacy-friendly** — no cookies, no guest data collection, no reservations or payments. GDPR-friendly by design.
* **Lightweight** — minimal CSS and JS. The frontend doesn't load any heavy frameworks.

= How it works =

1. Open **Calendar > Calendar Settings**, set your label text and pick a color for each. Add or remove labels as needed.
2. Open **Calendar**, assign a label to each day from a dropdown.
3. Place `[buffet_calendar_frontend]` on any page. Done.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/buffet-calendar`, or install through the WordPress **Plugins** screen and search for "Buffet Calendar".
2. Activate the plugin through the **Plugins** menu.
3. Go to **Calendar > Calendar Settings** to customize label text, colors, and which labels are enabled. Six defaults are pre-loaded so you can preview immediately.
4. Go to **Calendar** to assign a label to each day for the next several months.
5. Add the shortcode `[buffet_calendar_frontend]` to any page or post.

== Frequently Asked Questions ==

= Can I use this for something other than a hotel? =

Yes. Labels and colors are fully customizable. Use it for restaurant opening hours, café schedules, spa daily programs, gym class times, museum hours, holiday opening hours, or any recurring "what's open today" display.

= Can I add my own colors? =

Yes. Each label has its own WordPress color picker on the Settings page. Pick any hex color — the calendar cell tint and the legend swatch update automatically.

= Can I have more than 6 label categories? =

Yes. Click **Add Label** on the Settings page to add as many labels as you want. Each gets its own color, text, and enabled toggle. There's no fixed limit.

= Can I temporarily hide a category without deleting it? =

Yes. Uncheck the **Enabled** box for that label and save. It disappears from the legend and the day-picker dropdown, but days already assigned to that label keep showing the saved color so historical data isn't lost. Re-enable any time.

= Can I show prices in the legend? =

Yes — labels are free-text, so include prices, durations, à la carte notes, or anything else. Example: `Lunch Buffet 12 pm – 2 pm — €18` or `Breakfast 7:30 am – 10 am (included with stay)`.

= Does it support languages other than English? =

Yes. A `.pot` translation template ships at `languages/buffet-calendar.pot`. The calendar's month and day names automatically follow your WordPress site language on the frontend. Admin pages stay in English.

= Does it handle bookings, reservations, or payments? =

No — by design. This is purely a schedule display plugin. It does not take bookings, collect customer data, or process payments. Use a dedicated booking plugin for that.

= Does it work in the block editor / Gutenberg? =

Yes. Add a **Shortcode** block and paste `[buffet_calendar_frontend]`.

= Does it work with Elementor, Beaver Builder, Divi, and other page builders? =

Yes. Insert the shortcode into any text or HTML widget in your page builder.

= How many months does it show? =

12 months total. The first 3 are visible immediately; the next 9 appear when the visitor clicks **Show more**. Visitors get the rest of the year without overwhelming the page on first load.

= Will it slow my site down? =

No. The plugin makes no external API calls, no tracking requests, and ships with minimal CSS and JS. The data sits in two `wp_options` rows.

= Will it conflict with my theme? =

The plugin uses a scoped `.calendar-frontend` wrapper and prefixed class names. Most themes work out of the box. If your theme overrides the colors, you can adjust them in your theme's CSS.

= How do I uninstall it cleanly? =

Just deactivate and delete the plugin from the Plugins screen. The plugin's options are removed automatically via the bundled `uninstall.php`.

== Screenshots ==

1. Frontend view — color-coded monthly calendar with a legend explaining each color, meal times, and pricing.
2. Backend Calendar page — admins assign a label to each day from a simple dropdown.
3. Backend Settings page — fully dynamic: add unlimited labels, pick any color via the WordPress color picker, toggle each label on or off, remove labels you don't need.

== Changelog ==

= 1.0.0 =
* Initial public release.
* Color-coded 12-month calendar with shortcode `[buffet_calendar_frontend]`.
* Fully dynamic Settings page: add unlimited labels, pick any color via the WordPress color picker, enable / disable per label, remove labels.
* Six pre-loaded default labels (Breakfast, Lunch, Brunch, Coffee & Cake, etc.) with the original yellow / green / orange / blue / beige / red palette.
* Translucent calendar cell tinting with matching legend swatches.
* "Show more" toggle for months 4–12.
* Locale-aware frontend month and day names; English-only admin UI.
* Translation-ready with a `.pot` template at `languages/buffet-calendar.pot`.
* Clean uninstall — plugin options are deleted via `uninstall.php`.

== Upgrade Notice ==

= 1.0.0 =
First public release on WordPress.org.
