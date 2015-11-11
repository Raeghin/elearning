<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// Basic plugin strings
$string['pluginname'] = 'Cursus inzet';
$string['pagetitle'] = '{$a}: cursus tijd';

// Capabilites
$string['timespend:addinstance'] = 'Toevoegen van dit blok toestaan';
$string['timespend:use'] = 'Toestaan van het blok Cursusinzet';

// Block content
$string['timespend_estimation'] = 'Tijd gespendeerd in de cursus';
$string['access_button'] = 'Cursus inzet';
$string['access_info'] = 'Enkel voor docent:';

// Block form
$string['show_timespend'] = 'Laat gespendeerde tijd aan studenten zien.';
$string['show_timespend_help'] = 'Standaard wordt de tijd enkel aan de docenten getoond. Deze optie geeft cursisten de mogelijkheid hun tijd in te zien.';

// Tool form
$string['form'] = 'Cursus inzet configuratie';
$string['form_help'] = 'Tijs wordt berekend op basis van de sessie en de sessie duur in de logs.

<strong>Klik:</strong> Elke keer dat een gebruiker een pagina opvraagd wordt er in de logs een melding geplaatst.

<strong>Sessie:</strong> als er twee of meer achtereenvolgende kliks worden gedaan op een pagina met niet langer als de opgegeven maximum tijd ertussen.

<strong>Sessie duur:</strong> tijdspanne tussen de eerste en laatste klik in een sessie.

<strong>Cursus inzet:</strong> het totaal van alle sessie duren van een gebruiker.';
$string['form_text'] = 'Selecteer de begin en eind datum.';
$string['mintime'] = 'Begindatum';
$string['mintime_help'] = 'Begin van de periode waar de tijd mee moet tellen';
$string['maxtime'] = 'Einddatum';
$string['maxtime_help'] = 'Einde van de periode waar de tijd mee moet tellen';
$string['limit'] = 'Limiet tussen kliks (in minuten)';
$string['limit_help'] = 'het limiet tussen kliks bepaald of twee kliks in dezelfde sessie vallen of niet';
$string['submit'] = 'Bereken';

// Rows
$string['timespendrow'] = 'Cursus inzet';
$string['connectionratiorow'] = 'Connecties per dag';
$string['sincerow'] = 'Sinds';
$string['torow'] = 'Tot';
$string['perioddiffrow'] = 'Verstreken tijd';

// Headers
$string['period'] = 'Periode van {$a->mintime} tot {$a->maxtime}';
$string['perioddiff'] = 'Verstreken tijd  {$a}';
$string['totaltimespend'] = 'Totale tijd gespendeerd in de cursus: {$a}';
$string['meantimespend'] = 'Gemiddelde tijd per sessie: {$a}';

// Actions
// all action
$string['timespendall'] = 'Cursus inzet van alle cursusisten. Klik op een naam voor een gedetailleerd rapport.';
// group action
$string['timespendgroup'] = 'Cursus inzet van alle groeps leden van de groep {$a}. Klik op een naam voor een gedetailleerd rapport.';
// user action
$string['usertimespend'] = 'Gedetailleerd rapport van {$a}.';
$string['sessionstart'] = 'Sessie gestart';
$string['sessionduration'] = 'Duur:';
$string['nocertificate'] = 'Niet behaald';
$string['certificatecode'] = 'Certificaat code';
