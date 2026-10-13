# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Private Site is a XenForo add-on (`Hampel/PrivateSite`, requires XF 2.2.0+) that hides features a
guest has no use for on a forum that is closed to the public. The repository root is the add-on
directory, `src/addons/Hampel/PrivateSite/` inside a XenForo install. It has no Composer
dependencies, no class extensions and no test suite — **the whole of what it does is three template
modifications and one option**, all under `_output/`. Its only PHP is `Setup.php` and a one-method
`Listener.php`.

## Three template modifications are the whole behaviour

| modification | template | what it does |
|---|---|---|
| `hampelPrivateSiteForumOverviewWrapper` | `forum_overview_wrapper` | shows the home message to guests, above the `forum_overview_top` ad position |
| `hampelPrivateSitePageForumOverviewWrapper` | `forum_overview_wrapper` | puts a condition on `<xf:pageaction>`, hiding the *New posts* button |
| `hampelPrivateSitePageContainerWhatsNew` | `PAGE_CONTAINER` | wraps the *What's new* link in a condition |

**The first two keys differ by one word and are easy to confuse** — the one with `Page` in it is
the button, not the message.

**Two different audiences are in play, and that is deliberate.** The message is shown to guests
only (`!$xf.visitor.user_id`). The button and the link are hidden from guests *and* from any member
whose `user_state` is not `valid` — awaiting email confirmation or approval, which on a private
site is everyone who has registered and not yet been let in. That second condition arrived in
1.1.0; do not simplify it back to a guest check.

**Each modification matches core markup and fails silently when core changes.** Two are a
`str_replace` on a literal tag; the third is a `preg_replace` with the pattern
`#(<a href="{{ link\('whats-new'\) }}".*</a>)#simU`, which is ungreedy and would wrap every match
in the template. An XF release that rewrites any of those leaves the modification matching
nothing, with no error, and the feature quietly reappears — 1.2.0 exists because XF 2.3 did
exactly that to the mobile *What's new* icon. After an XF upgrade, check the apply counts in the
admin control panel under *Appearance > Template modifications*.

## The home message is rendered as raw HTML, on purpose

**`hampelPrivateSiteHomeMessage` is output with `|raw`**, so an admin can put links in it; the
option's explain phrase says so. It is admin-supplied, never user-supplied, which is what makes
that acceptable — do not route anything a member can type into it. The default links to `register`
and `login` as relative URLs, which resolve because the message only appears on the forum home
page.

The message block is wrapped in `<xf:if contentcheck="true">`, so the surrounding `block` markup
is not rendered for a logged-in visitor rather than appearing empty.

## `Setup.php` guards one call by XF version

**`postUpgrade()` calls `enqueuePostUpgradeCleanUp()` only when `\XF::$versionId >= 2030000`.** The
method does not exist on `AbstractSetup` before XF 2.3, and the add-on still supports 2.2, so an
unguarded call is a fatal on upgrade for anyone on 2.2. Keep the guard for as long as
`addon.json` `require` allows a version below 2.3. Install, upgrade and uninstall steps are empty:
the add-on owns no schema.

## `Listener.php` declares the add-on's secrets, and there are none

**`Listener::adminApiRedaction()` answers the `hampel_admin_api_redaction` code event with an empty
list.** The event belongs to a separate add-on that reports a forum's settings with secrets
removed, and it shows an add-on's text options only once that add-on has said which are secrets.
The home message is not one. The add-on does not require the other: an event nothing defines is
never fired, so add nothing to `addon.json` `require`. **Adding, renaming or removing an option
means revisiting the list in the same commit.**

## Commands

Run from the XenForo install root; `cmd.php` resolves the install from its own location, so a path
to it works from this directory too.

```bash
php cmd.php xf-dev:import --addon=Hampel/PrivateSite     # after hand-editing _output/
php cmd.php xf-addon:export Hampel/PrivateSite           # DB -> _data/*.xml, this add-on only
php cmd.php xf:addon-upgrade Hampel/PrivateSite          # after a version bump
php cmd.php xf-addon:build-release Hampel/PrivateSite    # release zip into _releases/
```

**Never run `xf-dev:export`** — its `--addon` defaults to every add-on on the install, and it
overwrites `_output/` from the database. `xf-addon:export` writes the release XML under `_data/`,
which is gitignored, and is what `build-release` runs for itself.

**Prefer editing a modification in the admin control panel** over hand-editing its JSON: with
development mode on, saving there rewrites the `_output/` file. The `replace` strings carry
literal tabs and newlines, and the regex is escaped twice over once it is inside JSON.
`_output/option_hint.php` is generated; do not edit it.

There is no test suite, and `xf-dev:lint-templates` has nothing to lint: the add-on has
modifications and no templates of its own. **`TESTING.md` holds the checks**: the apply counts, a
query against the modification log, and the forum home page viewed as a guest, as an unconfirmed
member and as a valid member. Keep its *Surfaces* table in step with `_output/`.

## Packaging

**Dev-only root files are kept out of two places by two separate mechanisms**, and adding a new one
means updating both:

- **The XenForo release zip** — `build.json`'s `rm` line deletes `TESTING.md` and the two Claude
  files. It must run before the `mv` that promotes every remaining root `*.md` to the zip root, or
  it matches nothing and the file ships.
- **`git archive`** (GitHub's "Download ZIP") — `export-ignore` entries in `.gitattributes`.

Machine-specific notes go in `CLAUDE.local.md`, which is gitignored. This file is committed and
public: no install paths, hostnames or site names. `.gitattributes` also forces LF on commit and
checkout, because a Windows git client can write CRLF into the working tree.
