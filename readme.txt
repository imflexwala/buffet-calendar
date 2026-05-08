=== Buffet Calendar ===
Contributors: mustafaflexwala
Tags: opening hours, buffet, meal calendar, restaurant hours, availability calendar
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A color-coded monthly calendar to display daily meal times, buffet hours, prices, and closed days for hotels, restaurants, cafés, and resorts.

== Description ==

**Buffet Calendar** is the simplest way to publish daily opening hours, meal service windows, and pricing on a WordPress site. Built originally for a German hotel that needed to communicate breakfast, lunch, dinner buffet times, and "coffee & cake" hours at a glance, the plugin works equally well for any restaurant, café, resort, spa, or seasonal venue with varying daily schedules.

Visitors see a clean three-month calendar with each day color-coded by what's available — breakfast, lunch buffet, dinner buffet, coffee & cake, or closed — and a legend that explains the colors and prices. A "Show more" button reveals the rest of the year.

= Why this plugin? =

Most calendar plugins are built for events or bookings. None of them are designed for the very common case of "tell my guests what's open today and how much it costs." This plugin solves that one job and does it well.

= Key features =

* **Color-coded daily calendar** — assign each day one of 6 customizable categories (e.g. Breakfast, Lunch Buffet, Dinner Buffet, Coffee & Cake, Closed)
* **Fully customizable legend text** — you control every label, so the plugin adapts to any language, currency, or business type
* **12 months at a glance** — first 3 months shown by default, rest revealed with a "Show more" button
* **Simple admin UI** — edit any day with a dropdown; no coding required
* **Responsive layout** — three columns on desktop, stacks cleanly on tablet and mobile
* **Shortcode-based** — drop `[buffet_calendar_frontend]` on any page or post
* **Lightweight** — no external services, no tracking, no bloat
* **Translation-ready** — labels and locale are fully configurable

= Use cases =

* Hotel breakfast, lunch, and dinner buffet schedules
* Restaurant weekly opening hours and meal service windows
* Café coffee & cake times
* Spa and resort daily program availability
* Seasonal venues (beach clubs, ski lodges, vineyards) with variable hours
* Any business with a different schedule each day

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install through the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Calendar** in the admin sidebar to set each day's category.
4. Go to **Calendar > Calendar Settings** to customize the 6 legend labels (meal names, prices, etc.).
5. Add the shortcode `[buffet_calendar_frontend]` to any page or post.

== Frequently Asked Questions ==

= Can I use this for something other than a hotel? =

Yes. The 6 color categories and their labels are fully customizable. Use it for restaurant hours, spa availability, café schedules, or any daily-schedule display.

= Can I change the colors? =

The 6 colors (yellow, green, orange, blue, beige, red) are styled via CSS and can be overridden in your theme.

= Does it support languages other than English? =

Yes. All visible text comes from the legend labels you configure, so you can write them in any language. The calendar also supports locale-aware month and day names.

= Does the calendar handle bookings or payments? =

No — this plugin is purely for displaying schedules. It does not handle reservations, payments, or guest data.

= How many months does it show? =

12 months. The first 3 are shown by default; the rest appear when the visitor clicks "Show more."

== Screenshots ==

1. Frontend view — three-month calendar with color-coded days and a legend explaining each color and its price.
2. Backend view — admins set each day's category from a simple dropdown.
3. Settings page — customize the label text for each of the 6 color categories.

== Changelog ==

= 1.0.0 =
* Initial public release on WordPress.org.

== Upgrade Notice ==

= 1.0.0 =
First public release.
