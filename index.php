<?php

require_once __DIR__ . '/public/bootstrap.php';
$shellCmnd = 'php scrape-links.php';

shell_exec($shellCmnd) ?: false;

$folder = __DIR__ . '/scraped-pages';

$pageLinks = [];

foreach (glob("$folder/*.json") as $path) {
    if (is_dir($path) || pathinfo($path, PATHINFO_EXTENSION) !== 'json') {
        continue;
    }

    $pageLinks[basename($path, '.json')] = file_get_contents($path);
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Reader Mode</title>
    <link rel="apple-touch-icon-precomposed" sizes="192x192" href="https://volkskrant.nl/static/apple-touch-icon-192x192.png">
    <link rel="apple-touch-icon-precomposed" sizes="180x180" href="https://volkskrant.nl/static/apple-touch-icon-180x180.png">
    <link rel="apple-touch-icon-precomposed" sizes="167x167" href="https://volkskrant.nl/static/apple-touch-icon-167x167.png">
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="https://volkskrant.nl/static/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="https://volkskrant.nl/static/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon-precomposed" sizes="128x128" href="https://volkskrant.nl/static/apple-touch-icon-128x128.png">
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="https://volkskrant.nl/static/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="https://volkskrant.nl/static/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="https://volkskrant.nl/static/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="https://volkskrant.nl/static/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="https://volkskrant.nl/static/apple-touch-icon-60x60.png">
    <link rel="icon" type="image/png" href="https://volkskrant.nl/static/favicon-196x196.png" sizes="196x196">
    <link rel="icon" type="image/png" href="https://volkskrant.nl/static/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="https://volkskrant.nl/static/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="https://volkskrant.nl/static/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="https://volkskrant.nl/static/favicon-16x16.png" sizes="16x16">

    <link rel="stylesheet" href="css/app.css">
    <script>
        window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>";
    </script>
    <script>
        (function() {
            const stored = localStorage.getItem('theme');

            if (stored === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (stored === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                // systeemvoorkeur
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-neutral-100 dark:bg-neutral-800 font-sans text-neutral-900 dark:text-neutral-100 p-5">
    <main class="flex flex-col w-[750px] max-w-4xl justify-self-center">
        <header>
            <div x-data class="flex gap-2 items-center">

                <button
                    @click="$store.theme.set('light')" title="Light"
                    class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700" :class="$store.theme.mode === 'light' ? 'bg-gray-400 dark:bg-gray-900' : ''">
                    ☀️
                </button>

                <button
                    @click="$store.theme.set('dark')" title="Dark"
                    class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700" :class="$store.theme.mode === 'dark' ? 'bg-gray-400 dark:bg-gray-900' : ''">
                    🌙
                </button>

                <button
                    @click="$store.theme.set('system')" title="System"
                    class="px-2 py-1 rounded bg-gray-200 dark:bg-gray-700" :class="$store.theme.mode === 'system' ? 'bg-gray-400 dark:bg-gray-900' : ''">
                    🖥
                </button>

            </div>
        </header>
        <div class="w-full logo mb-5 flex justify-center">
            <a title="Naar de homepagina" href="/" class="inline-flex flew-col justify-center mt-2 px-8 py-4 rounded-full bg-neutral-100 border border-black">
                <svg fill="none" height="30" width="244"
                    xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#a)">
                        <path clip-rule="evenodd"
                            d="M74.2 15.7c6 0 9.9 2.8 9.9 7.1 0 4.4-3.9 7.2-10 7.2-6 0-9.9-2.8-9.9-7.1 0-4.4 3.9-7.2 10-7.2Zm164-4.8.4.3v5.2h4.5v1.2h-4.5v6.8c0 .8 0 1.8.2 2.4.1.6.6 1 1.3 1 2 0 2-1.7 2-3.3V24h1v.4c0 1.6 0 2.4-.4 3-.8 1.7-2.6 2.5-5.5 2.5-2.1 0-3.6-.4-4.5-1.5-.6-.6-1-1.5-1-2.6l-.1-2.3v-6h-2.4v-1l9-5.7ZM33.3 15.8c5.7 0 9 2.6 9 7.3H30.8v.7c0 1.3 0 2.3.2 2.8.4 1.3 1.7 2.1 3.8 2.1a9 9 0 0 0 6.7-3.4l.6.8c-1.9 2.5-4.9 3.9-8.6 3.9-5.9 0-9.8-2.8-9.8-7 0-4.5 3.7-7.2 9.6-7.2ZM20.6 0v26.8c0 1.4.3 1.6 2.2 1.6h.7v1h-9.7v-1.2a8 8 0 0 1-5.4 1.7C4.6 30 .8 27.2.8 23c0-4.4 3.8-7.1 7.6-7.1 2 0 3.8.6 5.1 1.5V2.5c0-1.2-.3-1.4-2.2-1.4H10V0h10.6Zm110.7 15.9c1.8 0 3.3.2 5 .8l1 .2c.4 0 .5-.2.6-.6h1v4.4h-1v-.3c0-1-.1-1.5-.6-1.9a5.6 5.6 0 0 0-3.4-1.1c-1.2 0-1.8.4-1.8 1.1 0 .7.4 1 2.2 1.7l1.7.7C139 22 140 23 140 25c0 3-2.8 4.9-7.5 4.9-2.2 0-4-.4-6.4-1.2l-.6-.1c-.3 0-.5.3-.5.9h-1v-5h1c0 1.1.2 1.7.6 2.2.8 1 2.5 1.7 4 1.7 1.2 0 1.8-.4 1.8-1 0-.7-.4-1.1-1.6-1.6l-1-.5c-3.3-1.5-4.5-2.8-4.5-4.8 0-2.8 2.7-4.6 7.1-4.6Zm64.4-.1c2.9 0 5 .7 6 2.1.4.5.7 1 .8 1.5.2.7.2 1.2.2 2.9v4.4c0 1.3.3 1.7 1.6 1.7h.6v1h-9v-1.2a9.2 9.2 0 0 1-5.4 1.7c-3 0-5.5-1.5-5.5-4.4 0-3.2 3.2-4.4 7.8-4.4h2.9v-.9c0-.9 0-1.3-.2-1.7-.3-.7-1.2-1.1-2.6-1.1-1.4 0-2.9.4-3.6 1-.5.4-.7 1-.7 2v.3h-1v-4.3h.9c0 .5.1.6.5.6l1.2-.3c2-.6 3.8-.9 5.5-.9ZM150 0v22.4l3.1-2.3 1.3-1c.5-.4.7-.7.7-1 0-.3-.2-.4-.4-.5-.2-.1-.4-.2-1.3-.2h-1v-1h10.2v1c-3.3.1-5.5.7-7 1.8l6.4 8.1c.5.6.8.9 1.1 1l1 .1h.4v1.1h-9.6l-5-6.5v3.7c0 1.5.4 1.7 2.3 1.7h.4v1.1H140v-1h.7c2 0 2.2-.3 2.2-1.7V2.6c0-1.3-.3-1.5-2.1-1.5h-1.4V0H150Zm-41.5 0v22.4l3.2-2.3 1.3-1c.5-.4.6-.7.6-1 0-.3-.1-.4-.3-.5-.3-.1-.4-.2-1.4-.2h-1v-1H121v1c-3.3.1-5.5.7-7 1.8l6.5 8.1c.4.6.7.9 1 1l1 .1h.4v1.1h-9.5l-5-6.5v3.7c0 1.5.3 1.7 2.2 1.7h.5v1.1H98.5v-1h.7c2 0 2.3-.3 2.3-1.8V2.6c0-1.3-.3-1.5-2.2-1.5H98V0h10.6Zm71.7 15.8c2.6 0 4.2 1.7 4.2 4.2 0 2.4-1.5 4-3.8 4-2.1 0-3.7-1.5-3.7-3.4 0-.5 0-1 .3-1.4l.1-.3-.2-.1c-.2 0-.5.2-.8.7l-.8 1.8-.2 2.9v2.6c0 1.4.3 1.6 2.2 1.6h.9v1h-12.9v-1h.6c2 0 2.2-.2 2.2-1.5v-8c0-1.2-.3-1.4-2.2-1.4h-1v-1.1h10v3.4c1-2.5 2.8-4 5.1-4ZM94.5 0v26.8c0 1.4.3 1.6 2.2 1.6h.8v1h-13v-1h.7c2 0 2.2-.2 2.2-1.5V2.6c0-1.3-.3-1.5-2.1-1.5h-1.4V0h10.6Zm126.7 16c2 0 3.5.6 4.4 1.6a4 4 0 0 1 1 2.2v6.9c0 1.5.3 1.7 2.2 1.7h.6v1H218v-.9h.2c.6 0 1 0 1.1-.3.2-.2.3-.7.3-1.9v-5c0-1.6 0-2-.2-2.4-.2-.5-.7-.7-1.4-.7-.8 0-1.5.3-2 .8-.4.6-.5 1-.5 2.7v6c.2.6.5.8 1.4.8h.3v1H206v-1.1h.5c2 0 2.3-.2 2.3-1.6V19c0-1.4-.3-1.6-2.3-1.6h-.9v-1.1h9.8v1.9a8.2 8.2 0 0 1 5.9-2.3ZM55.6 0v1H55a10 10 0 0 0-1.8.2c-.7.1-1 .5-1 1.1 0 .6.2 1.2.8 2.7l6.8 15.2L66.1 6c.4-1 1.2-2.6 1.2-3.5 0-1-.7-1.4-3.3-1.4h-.3V0h9.2v1h-.3a7 7 0 0 0-1.8.2c-.6.2-.9.6-1.5 1.9l-12 26.4h-1.5L43.8 3a7 7 0 0 0-.9-1.4c-.4-.5-.9-.6-2.3-.6h-1V0h16Zm18.6 16.9c-1.2 0-2 .5-2.4 1.3-.2.5-.2 1-.2 3v3.4a9 9 0 0 0 .2 2.9c.4.9 1.2 1.4 2.4 1.4 1.1 0 2-.5 2.3-1.3.2-.6.2-1.1.2-2.9V21c0-1.7 0-2.3-.2-2.8-.3-.8-1.2-1.3-2.3-1.3Zm-63.5.5c-2.4 0-2.4 2-2.4 4v3c0 2 0 3.9 2.4 3.9a3 3 0 0 0 2-.7c.7-.6.9-1.4.9-3.1v-3.2c0-1.8-.2-2.6-.9-3.1a3 3 0 0 0-2-.8Zm184.2 4.8h-.1c-1.8 0-2.5.8-2.5 2.6v.6c0 1.8.4 2.5 1.6 2.5.6 0 1-.2 1.4-.6l.4-1.2v-3.8h-.8ZM33.2 17c-1 0-1.7.4-2 1-.4.5-.4 1-.4 2.8V22h4.7v-1.4c0-1.4 0-2-.2-2.5-.3-.7-1-1.2-2-1.2Z"
                            fill-rule="evenodd" fill="#000"></path>
                    </g>
                    <defs>
                        <clipPath id="a">
                            <path d="M.8 0h242.4v30H.8z" fill="#fff"></path>
                        </clipPath>
                    </defs>
                </svg>
            </a>
        </div>

        <div
            x-data="accordionComponent()"
            x-init="$store.state.parsePageLinks()"
            class="w-full mx-auto space-y-4">

            <template x-for="(listsByDate, name) in $store.state.linkLists" :key="name">
                <div class="bg-gray-400 border dark:border-gray-600 rounded-md shadow-sm overflow-hidden">

                    <!-- NAME HEADER -->
                    <button
                        @click="toggleName(name)"
                        class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-neutral-900 dark:text-neutral-100 hover:bg-gray-500 transition">
                        <span x-text="name"></span>

                        <!-- Chevron -->
                        <svg
                            class="w-5 h-5 transition-transform duration-300"
                            :class="isNameOpen(name) ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- NAME CONTENT -->
                    <div x-show="isNameOpen(name)" x-collapse class="bg-gray-500">

                        <template x-for="(links, date) in listsByDate" :key="date">
                            <div class="border-t border-gray-600">

                                <!-- DATE HEADER -->
                                <button
                                    @click="toggleDate(name, date)"
                                    class="w-full flex items-center justify-between px-10 py-3 text-left text-sm font-medium text-neutral-100 hover:bg-gray-500 transition">
                                    <span x-text="date"></span>

                                    <svg
                                        class="w-4 h-4 transition-transform duration-300"
                                        :class="isDateOpen(name, date) ? 'rotate-180' : ''"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- DATE CONTENT -->
                                <div x-show="isDateOpen(name, date)" x-collapse>
                                    <ul class="px-14 pb-4 space-y-2 text-sm">
                                        <template x-for="link in Object.values(links)" :key="link">
                                            <li class="list-disc">
                                                <span @click="$store.state.getArticle(link);toggleDate(name,date);toggleName(name)"
                                                    class="text-blue-100 hover:text-blue-200 hover:underline transition cursor-pointer"
                                                    x-text="$store.state.parseLinkToTitle(link)"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>

                            </div>
                        </template>

                    </div>
                </div>
            </template>
        </div>

        <div x-data>
            <template x-if="$store.state.currentArticle">
                <div>
                    <div x-html="$store.state.currentArticle.content" class="text-justify prose prose-h2:text-lg prose-h2:font-medium"></div>
                </div>
            </template>
        </div>
    </main>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                mode: localStorage.getItem('theme') || 'system',

                init() {
                    this.apply();

                    // For when system color scheme changes 
                    window.matchMedia('(prefers-color-scheme: dark)')
                        .addEventListener('change', () => {
                            if (this.mode === 'system') {
                                this.apply();
                            }
                        });
                },

                set(mode) {
                    this.mode = mode;
                    localStorage.setItem('theme', mode);
                    this.apply();
                },

                apply() {
                    const root = document.documentElement;

                    if (this.mode === 'dark') {
                        root.classList.add('dark');
                    } else if (this.mode === 'light') {
                        root.classList.remove('dark');
                    } else {
                        // system
                        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                            root.classList.add('dark');
                        } else {
                            root.classList.remove('dark');
                        }
                    }
                }
            });

            Alpine.store('state', {
                currentArticle: {
                    content: '<h3 class="mt-4 text-xl">Selecteer artikel</h3>'
                },
                linkLists: {},
                date: '<?= (new DateTime())->format('Y-m-d') ?>',
                init() {
                    this.parsePageLinks();
                },
                async getArticle(url) {
                    try {
                        const data = await graphqlRequest(MUTATION_ARTICLE_GQL, {
                            url: url
                        });
                        this.currentArticle = data.fetchArticle;
                    } catch (e) {
                        console.error('Failed to load artcle', e);
                    }
                },
                getLinksByName(name) {
                    const linkList = this.linkLists[name];
                    if (!linkList) return [];

                    return Object.values(linkList);
                },
                parsePageLinks() {
                    const data = <?= json_encode($pageLinks) ?>;

                    for (const [key, value] of Object.entries(data)) {
                        this.linkLists[key] = JSON.parse(value);
                    }
                },
                parseLinkToTitle(url) {
                    const path = URL.parse(url).pathname;
                    const linkPartsArr = path.split('/').filter(part => {
                        if (part.length) return part
                    })
                    const title = linkPartsArr[1].split('~')[0].split('-').join(' ');
                    return title.charAt(0).toUpperCase() + title.slice(1)
                },
                showError(message) {
                    this.error = message;

                    if (this.errorTimeout) {
                        clearTimeout(this.errorTimeout);
                    }

                    this.errorTimeout = setTimeout(() => {
                        this.error = null;
                        this.errorTimeout = null;
                    }, 5000);
                },
            })
        })
    </script>
    <script>
        function accordionComponent() {
            return {
                openName: null,
                openDates: {},
                state: Alpine.store('state'),

                toggleName(name) {
                    this.openName = this.openName === name ? null : name
                },

                isNameOpen(name) {
                    return this.openName === name
                },

                toggleDate(name, date) {
                    const key = name
                    this.openDates[key] =
                        this.openDates[key] === date ? null : date
                },

                isDateOpen(name, date) {
                    return this.openDates[name] === date
                }
            }
        }
    </script>
    <script>
        const FETCH_ARTICLE_GQL = `
query GetArticle($id: Int!) {
    article(id: $id) {
        id
        title
        content
        publishedAt
    }
}
`;
        const FETCH_ARTICLES_GQL = `
query {
    articles {
        id
        title
    }
}
`;
        const FETCH_LINKS_GQL = `
query HomepageLinks($limit: Int!) {
    homepageLinks(limit: $limit) {
        title
        url
    }
}
`;
        const MUTATION_ARTICLE_GQL = `
mutation FetchArticle($url: String!) {
    fetchArticle(url: $url) {
        id
        title
        content
        publishedAt
    }
}
`;
    </script>

    <script>
        /**
         * A helper to make GraphQL requests with centralized error handling.
         * @param {string} query - The GraphQL query string
         * @param {object} [variables={}] - Optional variables for the query
         * @returns {Promise<any>} - Returns the parsed data, or throws an error
         */
        async function graphqlRequest(query, variables = {}) {
            const state = Alpine.store('state');

            try {
                const res = await fetch('/public/graphql.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        query,
                        variables
                    })
                });

                if (!res.ok) {
                    const text = await res.text();
                    const message = `Network error (${res.status}): ${text}`;
                    console.error('graphqlRequest error: ' + message)
                    state.showError(message);
                    throw new Error(message);
                }

                const json = await res.json();

                if (json.errors && json.errors.length) {
                    const message = json.errors.map(e => e.message).join(', ');
                    console.error('graphqlRequest error: ' + message)
                    state.showError(message);
                    throw new Error(message);
                }

                return json.data;
            } catch (err) {
                console.error('GraphQL request failed:', err);
                state.showError(err.message || 'An unknown error occurred.');
                throw err;
            }
        }
    </script>

    <script>
        function articleReader() {
            return {
                error: null,
                url: null,
                state: Alpine.store('state'),

                submit() {
                    if (!this.url) {
                        console.log('there is no url', this.url);
                        this.state.showError('Voer een geldige URL in.');
                        return;
                    }

                    this.state.getArticle(this.url);
                },

            };
        }
    </script>

    <script>
        function articleList() {
            return {
                articles: [],
                article: null,
                state: Alpine.store('state'),

                async init() {
                    console.log('Loading articles...');

                    try {
                        const data = await graphqlRequest(FETCH_ARTICLES_GQL);
                        this.state.articles = data.articles;
                    } catch (e) {
                        console.error('Failed to load links', e);
                    }
                },

                async load(id) {
                    if (!id) return;

                    try {
                        const data = await graphqlRequest(FETCH_ARTICLE_GQL, {
                            id: Number(id)
                        });
                        this.state.currentArticle = data.article;
                    } catch (e) {
                        this.state.showError('Failed to load links', e);
                    }
                }
            };
        }
    </script>
    <script>
        function linkList() {
            return {
                links: {},
                state: Alpine.store('state'),
                init() {
                    this.parsePageLinks();
                },
                // parsePageLinks() {
                //     for (const [key, value] of Object.entries(<?= json_encode($pageLinks) ?>)) {
                //         this.state.linkLists[key] = JSON.parse(value);
                //     }
                // },
                async getLinks() {
                    try {
                        const data = await graphqlRequest(FETCH_LINKS_GQL, {
                            limit: 50
                        });
                        this.state.links = data.homepageLinks;
                    } catch (e) {
                        console.error('Failed to load links', e);
                    }
                }
            };
        }
    </script>

</body>

</html>