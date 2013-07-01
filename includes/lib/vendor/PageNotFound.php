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
                      <li><a href="/page-home.html">Startseite</a></li>
                      <li><a href="/page-home.html#kontakt">Kontakt</a></li>
                      <li><a href="/page-home.html#fehler">Fehler melden</a></li>
                    </ul>
                </div>
            </div>'
        );
    }
}
