> [!NOTE]
> This isn't flexible enough for what I need out of a contact form (although I do still think it's close!) and I want to rework this to feature a field builder, configured programmatically, like what WPforms and others offer. I've removed the v1.0 release because it wasn't very good, but what is here does work! 😊

# Elegant Contact Form
A WordPress contact form that only does what you need it to.

## Quick start
1. Download the source code
2. Import the ZIP into WordPress
3. Within the WP Admin, go to Settings > Elegant Contact Form and set your admin email and [reCAPTCHA v2](https://developers.google.com/recaptcha/docs/display) keys
4. To embed the form in a post or page, use the shortcode `[elegant_contact_form]`

## The problem
In practical applications (aka actual client websites) contact forms only ever really need to perform basic tasks. "I want to be able to capture the potential lead when someone wants to contact us, and I want to be sure I've captured it." There's countless form builder plugins that are good at capturing the input given to them and sending it off to an administrator's email, or maybe they  even a bit cleverer and they keep the form submission data and let you do some basic analytics on it... but then I run into issues tutoring my clients around weird UX decisions, predatory monetisation practices ("this feature you really need is actually paywalled for $199/year, lol"), and just... _bloat-y stuff_ in general. I don't bemoan developers for doing this but the monetisation means tends to feel a little manipulative to me.

## The solution
Enter: Elegant Contact Form. Literally all it does is give you some form fields to collect a potential lead's contact info (name, email or phone, and message), saves a copy to the local database, and emails it to a destination of your choice. Anti-spam protection is implemented via [reCAPTCHA v2](https://developers.google.com/recaptcha/docs/display), which is free for you to use and only needs a site and secret key.

There is no front-end style included, but editing `form-template.php` is fine as long as you don't change any `name` attributes. They're all the most obvious possible choices because I wanted the best chance of auto-complete activating to reduce end user friction.

## The method
I wanted to keep this as simple as possible, relying on long-standing and trusted WP methods and tried-and-true PHP programming practices because this is such a core function of a website that I just need it to work and get out of the way. A simple database table is created that saves the form's submission timestamp, the submitter's name and info, and their message. That's it (I was also conscious of privacy so I didn't wanna do any storage of personal data, and just for my own peace of mind there's both per-item and bulk deletion from the admin UI. At the moment the only WP Capabilities used is `manage_options`, which means you'd need a rank higher than editor (or shop manager) to be able to access and work with the locally stored form data. 

## The future
I'm not familiar enough with all of the eccentricities of extending WP admin APIs (like the `WP_List_Table` class). I did creative suitably "WP native" UI for the form submissions admin UI. I consider that a pretty minor use case, and this is already a lot better than any of the off-the-shelf offerings I could easily find.
