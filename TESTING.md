# Testing Private Site

What this add-on touches, where it breaks, and how to check it. There is no test suite: the add-on
is three template modifications, one option and one listener, so most of the checking is done by
looking at a forum.

## Surfaces

**Template modifications** — all public, all enabled by default:

| modification | template | what it does |
|---|---|---|
| `hampelPrivateSiteForumOverviewWrapper` | `forum_overview_wrapper` | adds the home message above the `forum_overview_top` ad position, for guests only |
| `hampelPrivateSitePageForumOverviewWrapper` | `forum_overview_wrapper` | puts a condition on `<xf:pageaction>`, hiding every button in it: *New posts* and *Post thread* |
| `hampelPrivateSitePageContainerWhatsNew` | `PAGE_CONTAINER` | wraps the *What's new* link in a condition |

The button and the link are shown only to a logged-in member whose `user_state` is `valid`. The
message is shown only to guests.

**Option** — `hampelPrivateSiteHomeMessage`, in its own group *Private Site*. A text box holding
the HTML shown to guests on the forum home page.

**Code event listener** — `hampel_admin_api_redaction`, answered by
`Listener::adminApiRedaction()` with an empty list. It fires only where the add-on that defines
the event is installed.

**Class extensions** — none. **Schema** — none; `Setup.php` has no install or uninstall steps.

## Fragile points

- **Every modification matches literal core markup and fails silently.** When a XenForo release
  rewrites the markup, the modification matches nothing, no error is logged, and the feature it
  hid comes back. XenForo 2.3 did this to the mobile *What's new* icon, which is why 1.2.0 exists.
- **The *What's new* pattern wraps every match, not the first.** It is an ungreedy `preg_replace`
  on any link to `whats-new` in `PAGE_CONTAINER`. A release that adds a second such link gets it
  wrapped too, which is probably wanted, and one that changes the `href` expression gets none.
- **A style that customises either template can lose a modification** while the master style still
  applies it. Check each style in use, not only the default.
- **The home message is output with `|raw`.** That is what lets an administrator put links in it,
  and it is safe only because the value comes from the admin control panel. An unclosed tag in it
  breaks the layout of the forum home page.
- **The default message links to `register` and `login` as relative URLs.** They resolve from the
  forum home page, which is the only place the message appears. They would not resolve on a forum
  whose home page is served from a deeper route.
- **`Setup::postUpgrade()` calls `enqueuePostUpgradeCleanUp()` behind a version check.** The method
  is new in XenForo 2.3, so removing the check is a fatal error on upgrade for a 2.2 forum.

## Automated

**Nothing in the repository runs on its own.** `xf-dev:lint-templates` reports nothing here,
because the add-on has modifications and no templates of its own.

Two checks can be run by hand without a browser.

**Each modification's `find` matches its core template exactly once.** Count the matches of each
`find` string in the master copy of the template it names. This was done against the public
templates of XenForo 2.2.0, 2.2.19, 2.3.0, 2.3.12 and 2.3.13, and each of the three matched once in
every release. It proves the markup is still there; it does not prove the result renders.

**The apply log agrees**, once the add-on is active and the templates have been recompiled:

```sql
SELECT m.modification_key, l.status, l.apply_count
FROM xf_template_modification AS m
INNER JOIN xf_template_modification_log AS l ON l.modification_id = m.modification_id
WHERE m.addon_id = 'Hampel/PrivateSite';
```

Every row should have a `status` of `ok` and an `apply_count` of 1. A modification with no row has
not been applied to any template.

## Needs a human

**After installing, upgrading the add-on, or upgrading XenForo**, in each style in use:

1. In the admin control panel, under *Appearance > Template modifications*, confirm each of the
   three shows one match.
2. Open the forum home page **logged out**. The home message is shown, there is no *New posts*
   button, and there is no *What's new* link in the navigation — check at phone width as well,
   where the link is an icon.
3. Open it as a member **awaiting email confirmation or approval**. No message, no button, no
   link.
4. Open it as a **valid member**. No message; the button and the link are both back.
5. Change the home message in *Options > Private Site* to include a link, and confirm as a guest
   that it renders as a link and not as escaped HTML.

None of this can be automated from the repository: it depends on a running forum, three accounts
in different states, and how the page looks.
