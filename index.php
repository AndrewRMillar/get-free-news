<?php require_once __DIR__ . '/public/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Reader Mode</title>
    <link rel="stylesheet" href="css/app.css">
    <script>
        window.CSRF_TOKEN = "<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>";
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-neutral-800 font-sans text-neutral-100 p-5">
    <main class="flex flex-col w-[800px] max-w-4xl justify-self-center">
        <div class="w-full logo mb-5">
            <a title="Naar de homepagina" href="/" class="flex flew-col w-full justify-center">
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

        <div x-data="articleReader()" class="mb-5 py-5">

            <div class="w-full flex rounded-md gap-2">
                <input x-model="url" type="url" id="url" required placeholder="Plak hier de artikel-URL…"
                    class="bg-amber-50 w-full p-2.5 text-base text-black border border-neutral-600 rounded-md">

                <button @click.prevent="submit" :disabled="loading"
                    class="inline-flex items-center rounded-md bg-indigo-500 px-4 py-2 text-md leading-6 font-semibold text-white transition duration-150 ease-in-out hover:bg-indigo-400">
                    <span x-show="!loading">Lees</span>
                    <span x-show="loading" class="inline-flex items-center">
                        <svg class="mr-3 -ml-1 size-5 animate-spin text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Laden
                    </span>
                </button>
            </div>

            <template x-if="error">
                <div
                    x-show="error"
                    x-transition.opacity.duration.300ms
                    class="bg-red-600 text-white p-3 rounded-md mt-4 opacity-100 ease-out"
                    x-text="error">
                </div>
            </template>
        </div>

        <div x-data="articleList()" class="my-6">

            <h2 class="text-xl mb-2">Opgeslagen artikelen</h2>

            <select
                class="bg-amber-50 w-full p-2.5 text-black rounded-md overflow-hidden"
                @change="load($event.target.value)" id="article-select">
                <option value="">— kies een artikel —</option>
                <template x-for="article in $store.state.articles" :key="article.id">
                    <option :value="article.id" x-text="article.title" class="w-full overflow-hidden"></option>
                </template>
            </select>

            <!-- Result -->
            <template x-if="$store.state.currentArticle">
                <div x-show="$store.state.currentArticle" class="mt-6">
                    <h1 class="text-2xl font-bold mb-4" x-text="$store.state.currentArticle.title"></h1>
                    <div class="max-w-full text-white leading-6 text-justify prose prose-h2:text-white prose-h3:text-white prose-h3:text-xl prose-h2:text-2xl prose-p:py-2"
                        x-html="$store.state.currentArticle.content"></div>
                </div>
            </template>
        </div>

    </main>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('state', {
                articles: [],
                currentArticle: null,
                initiated: false,
                init() {
                    if (this.initiated) return;
                    this.initiated = true;
                },
                addArticle(article) {
                    // prevent duplicates
                    if (!this.articles.find(a => a.id === article.id)) {
                        this.articles.unshift(article);
                    }
                    this.currentArticle = article;
                }
            })
        })
    </script>

    <script>
        function articleReader() {
            return {
                url: '',
                loading: false,
                error: null,

                async submit() {
                    if (!this.url) {
                        this.error = 'Voer een geldige URL in.';
                        return;
                    }
                    this.loading = true;
                    this.error = null;
                    let article = null;

                    const query = String.raw`
mutation FetchArticle($url: String!) {
    fetchArticle(url: $url) {
        id
        title
        content
        publishedAt
    }
}
`;

                    try {
                        const res = await fetch('public/graphql.php', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': window.CSRF_TOKEN
                            },
                            body: JSON.stringify({
                                query,
                                variables: {
                                    url: this.url
                                }
                            })
                        });

                        const json = await res.json();
                        // console.log('response', json, json.data.fetchArticle);

                        if (json.errors) {
                            this.showError(json.errors[0].message);
                            return;
                        }
                        Alpine.store('state').addArticle(json.data.fetchArticle);
                    } catch (e) {
                        this.showError('Kan geen verbinding maken met de server.');
                    } finally {
                        this.loading = false;
                    }
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

            };
        }
    </script>

    <script>
        function articleList() {
            return {
                articles: [],
                article: null,

                async init() {
                    console.log('Loading articles...');
                    const query = String.raw`
query {
    articles {
        id
        title
    }
}
`;

                    const res = await fetch('public/graphql.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': window.CSRF_TOKEN
                        },
                        body: JSON.stringify({
                            query
                        })
                    });

                    const json = await res.json();
                    Alpine.store('state').articles = json.data.articles;
                },

                async load(id) {
                    if (!id) return;

                    const query = String.raw`
query GetArticle($id: Int!) {
    article(id: $id) {
        id
        title
        content
        publishedAt
    }
}
`;

                    const res = await fetch('public/graphql.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': window.CSRF_TOKEN
                        },
                        body: JSON.stringify({
                            query,
                            variables: {
                                id: Number(id)
                            }
                        })
                    });

                    const json = await res.json();

                    Alpine.store('state').currentArticle = json.data.article;
                }
            };
        }
    </script>

</body>

</html>