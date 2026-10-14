# Private Site for XenForo

Hides features a guest has no use for on a forum that is closed to the public, and shows them a
message in their place.

By [Simon Hampel](https://xenforo.com/community/members/sim.4264/).

- [Addon: Private Site](https://xenforo.com/community/resources/private-site.7845/)
- [Discussion and support: Private Site](https://xenforo.com/community/threads/private-site.181304/)

## What it does

- **Shows a message to guests on the forum home page**, above the forum list. The default invites
  them to register or log in, and you can change it.
- **Hides the buttons at the top of the forum home page**, *New posts* among them.
- **Hides the *What's new* icon in the navigation bar**, the one beside the search icon.

The buttons and the icon are hidden from guests, and from members who have registered and are
still awaiting email confirmation or approval. A member whose account is valid sees the forum as
normal, and never sees the message.

## What it does not do

**It does not restrict access to anything.** It removes links; the pages behind them answer as
they did before. Who can see what on your forum is decided by its permissions, and a private forum
needs those set first.

**It leaves the *What's new* navigation tab alone.** That tab is yours to turn off, in the admin
control panel under *Setup > Public navigation*.

## Requirements

XenForo 2.2.0 or later.

## Installation

Install it as you would any XenForo add-on: upload the zip in the admin control panel under
*Add-ons*, or copy the contents of `upload/` to the forum root and install it from there.

## Options

One option, under *Options > Private Site*:

- **Home page message** — the message shown to guests. It accepts HTML, so it can carry links.
