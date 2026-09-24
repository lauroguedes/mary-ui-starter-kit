<?php

declare(strict_types=1);

use LauroGuedes\DemoMode\Cleaners;
use LauroGuedes\DemoMode\Reset\Strategies;
use LauroGuedes\DemoMode\Restrictions;

return [

    /*
    |--------------------------------------------------------------------------
    | Demo mode
    |--------------------------------------------------------------------------
    |
    | Whether this installation declared itself a public demonstration. With
    | this off the package registers nothing: no middleware, no listener, no
    | route, no schedule, and no destructive command. The cost of having it
    | installed and switched off is one boolean.
    |
    */

    'enabled' => (bool) env('DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Where a reset is allowed to happen
    |--------------------------------------------------------------------------
    |
    | A guard independent of the flag above. Even with DEMO_MODE=true the reset
    | refuses to run outside this list, so a demo .env copied onto the wrong
    | server does nothing on its own. Adding 'production' here is possible and
    | is the only way past the production check — which is the point: it has to
    | be typed by a person who meant it.
    |
    */

    'environments' => ['local', 'staging', 'demo'],

    /*
    |--------------------------------------------------------------------------
    | Allowed hosts
    |--------------------------------------------------------------------------
    |
    | An allowlist for the host in APP_URL. Null disables the check. Set it on
    | anything public: it is the guard that survives an .env being copied to a
    | server whose APP_ENV happens to match the list above.
    |
    */

    'allowed_hosts' => null, // ['demo.example.com']

    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    |
    | The schedule accepts a raw cron expression or one of the shortcuts
    | 'hourly', 'daily', 'weekly' and 'every-N-hours'. Whatever it says is also
    | what Demo::nextResetAt() reads, so the banner's countdown and the
    | scheduler can never disagree.
    |
    */

    'reset' => [

        'strategy' => env('DEMO_RESET_STRATEGY', 'migrate-fresh-seed'),

        'schedule' => env('DEMO_RESET_SCHEDULE', 'hourly'),

        'maintenance' => (bool) env('DEMO_RESET_MAINTENANCE', true),

        /*
         | The lock that stops two resets overlapping lives in the cache, so it
         | is only as real as the cache store. 'null' grants every lock to
         | everybody and 'array' keeps them inside one PHP process; demo:doctor
         | reports both, because either makes this setting decorative.
         */
        'lock_ttl' => 1800,

        'connection' => null,

        /*
         | Each strategy has its own block, so 'seeder' belongs to
         | migrate-fresh-seed and does not have to be prefixed to stay out of
         | another strategy's way. Switching is then one env var.
         */
        'strategies' => [

            /*
             | Portable, needs nothing extra, and slow in proportion to how much
             | data makes the demo look like itself.
             */
            'migrate-fresh-seed' => [
                'driver' => Strategies\MigrateFreshSeed::class,
                'seeder' => Database\Seeders\DatabaseSeeder::class,
                'drop_views' => true,
            ],

            /*
             | An import rather than a rebuild. Needs spatie/laravel-db-snapshots,
             | which is suggested rather than required; demo:doctor says so before
             | the first scheduled reset rather than after it.
             |
             | Take the baseline with 'demo:snapshot'.
             */
            'snapshot' => [
                'driver' => Strategies\Snapshot::class,
                'name' => 'demo-baseline',
            ],

            /*
             | A .sql file the project keeps in version control. Shells out to the
             | database client, which must be on the PATH.
             */
            'sql-dump' => [
                'driver' => Strategies\SqlDump::class,
                'path' => null, // database_path('demo/baseline.sql')
                'client' => null, // 'mysql' or 'psql'; null picks by driver
                'timeout' => 900,
            ],

            /*
             | The escape hatch. Receives a ResetContext and does whatever the
             | application means by "back to the start".
             |
             | Write it as a callable string or [Class::class, 'method'], not a
             | Closure: a Closure here makes 'php artisan config:cache' fail
             | outright, which rules it out on every deployment that caches
             | config. Set 'seeds_credentials' when your callback re-hashes the
             | published password, so demo:doctor stops warning that rotation
             | cannot take effect.
             */
            'callback' => [
                'driver' => Strategies\Callback::class,
                'using' => null,
                'seeds_credentials' => false,
            ],

        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cleaners
    |--------------------------------------------------------------------------
    |
    | Resetting the database is not resetting the application. A surviving
    | session leaves a visitor signed in as a user who no longer exists, and a
    | cached settings blob leaves the server wearing whatever the last visitor
    | configured. These run after the strategy, in the order written.
    |
    | FlushCache keeps this package's own keys on purpose: the cache credential
    | store lives behind one of them, and rotating a password into a store that
    | the next step empties would publish a password nobody can use.
    |
    */

    'cleaners' => [

        Cleaners\FlushSessions::class => ['driver' => null],

        Cleaners\FlushCache::class => ['tags' => [], 'except' => ['demo-mode:*']],

        Cleaners\FlushStorage::class => ['disks' => [
            'public' => ['users'],
            'local' => ['livewire-tmp'],
        ]],

        Cleaners\FlushQueue::class => ['queues' => []],

    ],

    /*
    |--------------------------------------------------------------------------
    | Published credentials
    |--------------------------------------------------------------------------
    |
    | A demo is only a demo if a stranger can get in, so one password is
    | deliberately recoverable and shown on the sign-in page. Two things keep
    | that bounded: nothing is read back unless this installation still says it
    | is a demo, and a rotating password stops being a fact of the internet the
    | next time the scheduler runs.
    |
    | The file store must resolve to a disk that is not web-reachable.
    | 'demo:doctor' fails when it does not.
    |
    */

    'credentials' => [

        'enabled' => true,

        'store' => env('DEMO_CREDENTIALS_STORE', 'file'),

        /*
         | Whether Demo::toArray() carries the password. Leave it on for the
         | usual case of prefilling a login form; turn it off if that payload
         | is serialised into every page of a server-rendered app you would
         | rather not have a crawler read.
         */
        'expose_in_payload' => true,

        'accounts' => [
            [
                'email' => env('DEMO_EMAIL', 'admin@user.com'),
                'label' => 'Administrator',
                'rotate' => true,
                'primary' => true,
                'password' => null,
            ],
        ],

        'password' => [
            'length' => 16,
            'symbols' => false, // a visitor retyping it may be on another keyboard layout
            'numbers' => true,
        ],

        'stores' => [
            'file' => ['disk' => 'local', 'path' => 'demo-credentials.json'],
            'cache' => ['store' => null, 'key' => 'demo-mode:credentials', 'ttl' => null],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Restrictions
    |--------------------------------------------------------------------------
    |
    | Applied during boot, only while the flag is on. Everything the package
    | ships is listed here so that removing one is a visible edit rather than
    | an absent default.
    |
    */

    'restrictions' => [

        Restrictions\DisableMail::class => ['transport' => 'array'],

        Restrictions\DisableNotifications::class => ['channels' => ['vonage', 'nexmo', 'slack']],

        Restrictions\ForceConfig::class => ['pin' => []],

        Restrictions\BlockPrivilegedAccounts::class => [
            'roles' => [],
            'emails' => [],
            'message' => null,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Write guards
    |--------------------------------------------------------------------------
    |
    | Records a visitor may not change. This is the emptiest default here that
    | you should probably fill in: if a visitor can change the published
    | account's email or password, the next visitor cannot get in, and the demo
    | is closed until the following reset.
    |
    | Resolved as an array of attributes to match, or a Closure(Model): bool.
    |
    */

    'guards' => [

        /*
         | Records a visitor may not change. This is the emptiest default here
         | that you should probably fill in: if a visitor can change the
         | published account's email or password, the next visitor cannot get in,
         | and on a six-hour cycle the demo is closed for up to six hours.
         |
         | A map of attributes that all have to match, or a Closure(Model): bool.
         | Both the stored values and the incoming ones are checked, so a visitor
         | cannot edit their way out of the rule.
         */
        'protected' => [
            App\Models\User::class => ['email' => env('DEMO_EMAIL', 'admin@user.com')],
        ],

        /*
         | A demo nobody can write to. Off by default: a playground exists to be
         | written to, and a read-only demo demonstrates less.
         |
         | 'except' is by route name, because a URL is not a stable thing to
         | write in a config file — which also means a route with no name cannot
         | be excepted and will be blocked.
         |
         | 'redirect' sends a refused write back with a flashed 'error' message
         | instead of rendering 403. Use 'back', a path, or null for the 403.
         */
        'read_only' => [
            'enabled' => (bool) env('DEMO_READ_ONLY', false),
            'except' => ['login', 'logout', 'register', 'password.request', 'demo.reset'],

            /*
             | Null blocks anything that is not a known-safe method, which is the
             | reading with nothing to get wrong. Naming methods narrows it, and
             | then the list is yours to keep complete — Laravel honours
             | _method overrides, so a short list is a list to step around.
             */
            'methods' => null,

            'redirect' => null,
        ],

        /*
         | Rejects writes at the connection, where nothing can route around them.
         |
         | Off by default and genuinely dangerous: the false positives are not
         | edge cases, they are the framework working normally. Database-backed
         | sessions, cache, queues, job batches and failed jobs all write on
         | ordinary requests, and every one has to be listed here before the demo
         | can serve a page. Reach for read_only and protected first.
         */
        'connection' => [
            'enabled' => false,
            'except_tables' => [
                'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
                'password_reset_tokens', 'demo_sandboxes',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Every blocked write dispatches a WriteBlocked event whatever this says.
    | The log line is separate because a misconfigured connection guard blocks
    | every request, and a package that filled your log aggregator by default
    | would be teaching you to turn the whole thing off.
    |
    */

    'log' => [
        'channel' => env('DEMO_LOG_CHANNEL'),
        'blocked_writes' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Banner
    |--------------------------------------------------------------------------
    |
    | The visible notice that the data is temporary. <x-demo-banner /> renders
    | nothing when this installation is not a demo, so it is safe to put in a
    | layout unconditionally.
    |
    */

    'banner' => [

        'enabled' => true,

        /*
         | 'pill' is the package's own floating bar, rendered inside a shadow
         | root so daisyUI's theme cannot reach it. 'bare' is semantic markup
         | wearing the class names from "classes" below.
         */
        'style' => 'pill',

        'variant' => 'warning',

        'label' => 'Demo',

        'cta' => [
            'label' => 'Deploy your own',
            'url' => 'https://github.com/lauroguedes/mary-ui-starter-kit',
        ],

        'reset_button' => true,

        'asset_route' => '/demo-mode/bar.js',

        'dismissible' => true,

        /*
         | Null uses the translation, with the time until the next reset worked
         | out from the cron expression the scheduler runs. Set a string here to
         | say something else — and know that it will not count down.
         */
        'message' => null,

        'position' => 'bottom',

        /*
         | Class names by variant, so the common case is one line here rather
         | than a published view. The package ships no styling of its own: it
         | cannot know whether it is inside Tailwind, daisyUI or Bootstrap, and a
         | component that guesses is one every application rewrites.
         |
         |   'classes' => ['warning' => 'alert alert-warning', 'default' => 'alert'],
         */
        'classes' => [],

    ],

    /*
    |--------------------------------------------------------------------------
    | The one small script
    |--------------------------------------------------------------------------
    |
    | A ticking countdown, the banner's dismiss button, and copy-to-clipboard on
    | the credentials component. Emitted inline, once per response, and only on a
    | demo.
    |
    | Set false under a strict Content-Security-Policy. Every value is still on
    | the page and the <time> element is still truthful; what goes away is the
    | ticking, and the dismiss and copy buttons, which are not rendered at all
    | without the script behind them. Publish the views and move the script into
    | your own bundle if you want them back.
    |
    */

    'script' => true,

    /*
    |--------------------------------------------------------------------------
    | Reset on demand
    |--------------------------------------------------------------------------
    |
    | An HTTP route that rebuilds the demo. It hands an anonymous visitor a
    | migrate:fresh, so it is off by default and every control around it matters.
    |
    | Note what it resets: the whole demonstration, not the visitor's own corner
    | of it. Whatever anybody else was partway through goes with it. On a demo
    | more than one person looks at, per-visitor isolation is the thing that
    | actually wants building.
    |
    | Keep 'web' in the middleware — that is where CSRF comes from, and without
    | it any page anywhere can rebuild your demo with a form post. Consider
    | adding 'auth': "I broke the demo" is something a signed-in visitor asks.
    |
    | Three limits, stopping three different things: the throttle stops one
    | visitor pressing repeatedly, the cooldown stops many visitors each pressing
    | once, and the Runner's lock stops two resets overlapping.
    |
    */

    'on_demand' => [

        'enabled' => (bool) env('DEMO_ON_DEMAND', false),

        'route' => '/demo/reset',

        'name' => 'demo.reset',

        'middleware' => ['web'],

        /*
         | Requests per visitor per minute-window, as Laravel's throttle reads
         | it: attempts, then minutes.
         */
        'throttle' => ['attempts' => 1, 'minutes' => 60],

        /*
         | How the throttle counts a visitor: 'ip', 'session' or 'global'.
         |
         | Behind a proxy, 'ip' is only as trustworthy as your TrustProxies
         | configuration — every visitor may look like the load balancer, and one
         | of them then exhausts the limit for all of them. 'session' counts a
         | browser instead.
         |
         | Neither is a boundary against somebody determined: a session cookie is
         | deleted and an IP is changed. The throttle stops accidents and casual
         | repetition. The cooldown below is the limit that holds, because it
         | counts resets rather than requesters — set it.
         */
        'per' => 'ip',

        /*
         | Seconds since the last reset — from any source, including the
         | scheduler — before another is allowed.
         */
        'cooldown' => 900,

        /*
         | Off the request by default. A rebuild takes as long as it takes, and
         | doing it inline means a visitor watching a spinner until the proxy in
         | front of your application gives up.
         */
        'queue' => true,

        /*
         | Where to send a browser afterwards. Null aborts with the status code,
         | which is what an API wants; 'back' or a path redirects with a flashed
         | 'status' or 'error' message, which is what a person wants.
         */
        'redirect' => null,

    ],

    /*
    |--------------------------------------------------------------------------
    | Per-visitor isolation
    |--------------------------------------------------------------------------
    |
    | 'shared' is the default and the only one with no cost: everyone sees the
    | same data.
    |
    | 'scoped' gives each visitor the seeded baseline plus what they created. It
    | is real, and it is not multi-tenancy: it keeps ordinary visitors out of each
    | other's way, it has not been audited as a security boundary, and a model you
    | forget to mark leaks rows while appearing to work.
    |
    | It needs three things: the demo_sandboxes migration published and run, a
    | demo_sandbox_id column on every marked table, and session middleware on the
    | routes that use it.
    |
    */

    'sandbox' => [

        'driver' => env('DEMO_SANDBOX', 'shared'),

        /*
         | Seconds of inactivity before a sandbox is pruned. The middleware
         | pushes this out on every request, so it is a TTL rather than a
         | deadline. Null keeps them forever, which on a public demo means a
         | table that only grows.
         */
        'ttl' => 3600,

        /*
         | The session key the identifier lives under. In the session rather than
         | a cookie of its own, because Laravel's session cookie is already
         | signed and encrypted — and the identifier is treated as untrusted
         | regardless: it is a lookup key, and one that matches no live row mints
         | a new empty sandbox rather than selecting anybody else's rows.
         */
        'key' => 'demo_sandbox',

        /*
         | The models that carry BelongsToSandbox. Listed here so demo:doctor can
         | check they all actually do — a half-marked set leaks rows between
         | visitors while appearing to work, which is the one way this feature
         | fails silently.
         */
        'models' => [
            // \App\Models\Post::class,
        ],

        /*
         | When to prune. Registered by the package, like the reset schedule.
         */
        'prune' => '*/15 * * * *',

    ],

];
