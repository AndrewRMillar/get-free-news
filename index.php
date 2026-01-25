<?php

declare(strict_types=1);
require_once 'News.php';

if (!empty($_POST['url'])) {
    $url = trim($_POST['url']);

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = 'Ongeldige URL.';
    } else {
        $news = new News();

        $html = $news->fetchUrl($url);
        if (!$html) {
            $error = 'Kon de pagina niet ophalen.';
        } else {
            $content = $news->extractReadableContent($html, $url);
        }

        $articles = $news->getArticlesFromFile();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <title>Reader Mode</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
</head>

<body class="font-sans bg-neutral-900 text-neutral-200 p-5">
    <div class="flex flex-col w-[800px] max-w-4xl">
        <div class="text-center mb-5">
            <a title="Naar de homepagina" href="/">
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
        <div class="article-list">
            <h2>Opgeslagen artikelen</h2>
            <select class="w-full p-2.5 text-base mb-5" id="article-select">
                <option
                    value="&lt;?= htmlspecialchars($article['url']) ?&gt;">
                </option>
            </select>
        </div>
        <form method="post" class="mb-5 flex p-5 rounded-md">
            <input required="" placeholder="Plak hier de artikel-URL…"
                name="url" type="url" class="w-full p-2.5 text-base border border-neutral-600 rounded-md">
            <button type="submit"
                class="bg-blue-600 py-2.5 px-6 text-white text-base rounded-md">Lees</button>
        </form>
        <p class="text-red-600">
        </p>
    </div>
</body>

</html>