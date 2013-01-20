<?php

class PageNotFound extends Exception
{
    public function sendHeader() {
        header("HTTP/1.1 404 Not Found");
    }

    public function __construct() {
        parent::__construct('
            <div class="row">
                <div class="five columns not-found"></div>
                <div class="eleven columns">
                    <h1>Seite nicht gefunden</h1>
                    <p>Eventuell sind Sie mit einem veralteten Link auf diese Seite gekommen.</p>
                    <ul class="disc">
                        <li><a href="?">Startseite</a></li>
                        <li><a href="?page=contact">Kontakt</a></li>
                        <li><a href="?page=post_error">Fehler melden</a></li>
                    </ul>
                </div>
            </div>'
        );
    }
}
