<?PHP

$string['modulename'] = 'Attendance Register';
$string['modulenameplural'] = 'Attendance Registers';
$string['modulename_help'] = 'Attendance Register calculates time users spend working in online Courses.<br />
    Optionally allow the User record Offline activities.<br />
    Depending on Attendance Mode, the Register may tracks activities in a single Course, in all Courses in the same Category
    or in all Courses "Meta linked" to the Course the Register is in.<br />
    Online work sessions are calculated on Log entries recorded by Moodle.<br />
    <b>New online sessions are added with some delay by the cron, after User logout.</b>';
$string['pluginname'] = 'Attendance Register';
$string['pluginadministration'] = 'Attendance Register Administration';

// Mod instance form
$string['registername'] = 'Attendance Register naam';
$string['registertype'] = 'Attendance Tracking Modus';
$string['registertype_help'] = 'De presentie lijst modes bepaald de cursussen gevolgd door de presentie lijst (met andere woorden, waar de activiteiten van de gebruiker bijgehouden worden): 
* _Enkel deze cusus_: Enkel in de cursus waar de presentie lijst is toegevoegd. 
* _Alle cursussen in dezelfde categorie_: activiteit zal bijgehouden worden in alle cursussen in dezelfde categorie als de cursus waaraan de presentie lijst is toegevoegd. 
* _Alle gelinkte meta cursussen_: Activiteit wordt gehouden in deze cursus en alle cursussen die gelinked zijn.
    ';
$string['sessiontimeout'] = 'Online session timeout';
$string['sessiontimeout_help'] = 'Session Timeout is used for estimating Online Session duration.<br />
    Online Sessions will be at least <b>one half</b> the Session Timeout long.<br />
    Note that if Session Timeout is too long, the Register tends to overestimate Online Sessions duration.<br />
    If too short, real Sessions will break in many shorter Sessions.<br />
    <h3>Long explaination</h3>
    Online work sessions are <b>guessed</b> looking at Log entries of the User in the tracked Courses
    (see <i>Attendance Tracking Mode</i>).<br/>
    If a timespan shorter than Session Timeout elapsed between two consecutive Log Entries,
    the Register consider the User continuosly working online (i.e. the Session continue).<br />
    If a timespan longer then Session Timeout elapsed, the Register guesses the User stoped working online
    <b>one half</b> the Session Timeout after the previous Log Entry (i.e. the Session ends) and came back
    again at the following Log Entry (i.e. a new Session starts)';

$string['offline_sessions_certification'] = 'Offline sessies';
$string['enable_offline_sessions_certification'] = 'Schakel offline sessies in';
$string['offline_sessions_certification_help'] = 'Enables the Users to insert offline Sessions of work.<br />
    This is a kind of <i>Self-Certification</i> of the work done.<br />
    This may be useful if "bureaucracy" requires maintaining a register of every student\'s activities.<br />
    Only real users may add Offline Sessions: <i>Logged in as...</i> admins cannot!';
$string['dayscertificable'] = 'Dagen terug';
$string['dayscertificable_help'] = 'Limits how old the offline sessions may be.<br />
    A student may not record an Offline Session older than this number of days.';
$string['offlinecomments'] = 'Opmerkingen van gebruikers';
$string['offlinecomments_help'] = 'Enable adding textual comments to Offline Sessions';
$string['mandatory_offline_sessions_comments'] = 'Verplicht opmerkingen';
$string['offlinespecifycourse'] = 'Specify Course in Offline Sessions';
$string['offlinespecifycourse_help'] = 'Allow the user to select the Course the Offline Session is related to.<br />
    This is meaningful only if the Register tracks more than one Course (i.e. Attendance Mode is "Category" or "Meta-linked")';
$string['mandatoryofflinespecifycourse'] = 'Mandatory Course selection';
$string['mandatoryofflinespecifycourse_help'] = 'Specifying a Course in Offline Sessions will be mandatory';


$string['type_course'] = 'Enkel deze cursus';
$string['type_category'] = 'Alle cursussen in dezelfde categorie';
$string['type_meta'] = 'All Courses linked by Course meta link';

$string['maynotaddselfcertforother'] = 'You cannot add an offline sessions for other users.';
$string['onlyrealusercanaddofflinesessions'] = 'Only real user may add an offline session';
$string['onlyrealusercandeleteofflinesessions'] = 'Only real user may delete offline sessions';

// Capabilities
$string['attendanceregister:tracked'] = "Wordt bijgehouden in presentie lijst";
$string['attendanceregister:viewownregister'] = "Kan eigen presentie lijst inzien";
$string['attendanceregister:viewotherregisters'] = "Kan ander mans presentie lijst inzien";
$string['attendanceregister:addownofflinesess'] = "Kan offline sessies aan eigen presentie lijst toevoegen";
$string['attendanceregister:addotherofflinesess'] = "Kan offline sessies aan ander mans presentie lijst toevoegen";
$string['attendanceregister:deleteownofflinesess'] = "Kan offline sessies van eigen presentie lijst verwijderen";
$string['attendanceregister:deleteotherofflinesess'] = "Kan offline sessies van ander mans presentie lijst verwijderen";
$string['attendanceregister:recalcsessions'] = "Kan herberekenen van presentie lijst sessies forceren";
$string['attendanceregister:addinstance'] = "Nieuwe presentie lijst";

// Buttons & Links labels
$string['force_recalc_user_session'] = 'Herbereken de sessies van gebruiker';
$string['force_recalc_all_session'] = 'Herberekend alle sessies';
$string['force_recalc_all_session_now'] = 'Sessies nu herberekenen';
$string['schedule_reclalc_all_session'] = 'Plan herberekenen van sessies';
$string['recalc_scheduled_on_next_cron'] = 'Sessies worden bij op de achtergrond herberekend';
$string['recalc_already_pending'] = '(Herberekening staat al in de wachtrij)';
$string['first_calc_at_next_cron_run'] = 'Huidige sessie worden na afsluiten van betreffende sessie toegevoegd';
$string['back_to_tracked_user_list'] = 'Terug naar gebruikers lijst';
$string['recalc_complete'] = 'Herberekenen van sessies afgerond';
$string['recalc_scheduled'] = 'Herberekenen van sessies is toegevoegd aan de opdrachtenlijst. Deze wordt zo snel mogelijk uitgevoerd.';
$string['offline_session_deleted'] = 'Offline Session deleted';
$string['offline_session_saved'] = 'New Offline Session saved';
$string['show_printable'] = 'Printer vriendelijke versie';
$string['show_my_sessions'] = 'Laat mijn sessies zien';
$string['back_to_normal'] = 'Terug naar normale versie';
$string['force_recalc_user_session_help'] = 'Delete and recalculate all online Sessions of this User.<br />
    Normally you <b>do not need to do it</b>!<br />
    New Sessions are automatically calculated in background (after some delay).<br />
    This operation may be useful <b>only</b>:
    <ul>
      <li>After changing the Role of the User, ant he/she previously acted in any of the tracked Courses with a different Role
      (i.e. changing from Teacher to Student, when Studet are tracked and Teacher are not).</li>
      <li>After modifying Register settings that affects Sessions calculation
      (i.e. <i>Attendance Tracking Mode</i>, <i>Online Session timeout</i>)</li>
    </ul>';
$string['force_recalc_all_session_help'] = 'Delete and recalculate all online Sessions of all tracked Users.<br />
    Normally you <b>do not need to do it</b>!<br />
    New Sessions are automatically calculated in background (after some delay).<br />
    This operation may be useful <b>only</b>:
    <ul>
      <li>After changing the Role of a User that previously acted in any of the tracked Courses  with a different Role
      (i.e. changing from Teacher to Student, when Studet are tracked and Teacher are not).</li>
      <li>After modifying Register settings that affects Sessions calculation
      (i.e. <i>Attendance Tracking Mode</i>, <i>Online Session timeout</i>)</li>
    </ul>
    You <b>do not need to recalculate when enrolling new Users</b>!<br /><br />
    Recalculation can be executed immediately or scheduled for execution by the next cron.
    Scheduled execution could be more efficient for very crowded courses.';


// Table columns
$string['count'] = '#';
$string['start'] = 'Start';
$string['end'] = 'Einde';
$string['duration'] = 'Duur';
$string['online_offline'] = 'Tijd';
$string['ref_course'] = 'Ref. Cursus';
$string['comments'] = 'Opmerkingen';
$string['fullname'] = 'Naam';
$string['click_for_detail'] = 'klik voor details';
$string['total_time_online'] = 'Totale Tijd gespendeerd in cursus';
$string['total_time_offline'] = 'Totale Tijd Offline';
$string['grandtotal_time'] = 'Totale Tijd';

$string['online'] = 'Online';
$string['offline'] = 'Offline';
$string['not_specified'] = '(niet gespecificeerd)';
$string['never'] = '(nooit)';
$string['session_added_by_another_user'] = 'Toegevoegd door: {$a}';
$string['unknown'] = '(onbekend)';

$string['are_you_sure_to_delete_offline_session'] = 'Weet u zeker dat u deze offline sessie wilt verwijderen?';
$string['online_session_updated'] = "Online Sessie bijgewerkt";
$string['updating_online_sessions_of'] = 'Bijwerken van sessies van {$a}';
$string['online_session_updated_report'] = '{$a->fullname} Online Sessies bijgewerkt: {$a->numnewsessions} nieuwe sessies gevonden';

$string['user_sessions_summary'] = 'Overzicht van de gebruiker';
$string['online_sessions_total_duration'] = 'Totale tijd online';
$string['offline_refcourse_duration'] = 'Offline Tijd, Cursus:';
$string['no_refcourse'] = '(geen cursus gespecificeerd)';
$string['offline_sessions_total_duration'] = 'Totale tijd Offline';
$string['sessions_grandtotal_duration'] = 'Totale tijd';
$string['last_session_logout'] = 'Laatste sessie beeindigt';
$string['last_calc_online_session_logout'] = 'Laatste Online sessie beeindigt (excl. huidige sessie)';
$string['last_site_login'] = 'Laatste login';
$string['prev_site_login'] = 'Vorige login';
$string['last_site_access'] = 'Laatste activiteit:';
$string['summary'] = 'Overzicht:';


$string['no_session_for_this_user'] = '- Geen geregistreerde sessies voor deze gebruiker -';
$string['no_tracked_user'] = '- Geen gebruikers toegevoegd aan presentielijst -';
$string['no_session'] = 'Geen sessies';

$string['tracked_courses'] = 'Bijgehouden cursus';
$string['duration_hh_mm'] = '{$a->hours} u, {$a->minutes} min';
$string['duration_mm'] = '{$a->minutes} min';

// Offline Session form
$string['select_a_course_if_any'] = '- Selecteer een cursus (indien van toepassing) -';
$string['select_a_course'] = '- Selecteer een cursus -';
$string['insert_new_offline_session'] = 'Voeg een nieuwe offline sessie toe';
$string['insert_new_offline_session_for_another_user'] = 'Voeg een nieuwe offline sessie in voor {$a->fullname}';

$string['offline_session_start'] = 'Start';
$string['offline_session_start_help'] = 'Selecteer start en eind datum &amp; tijd van de offline sessie die u wilt toevoegen.<br />
    Deze sessie mag geen bestaande sessie overspannen.';
$string['offline_session_end'] = 'Einde';
$string['offline_session_comments'] = 'Opmerkingen';
$string['offline_session_comments_help'] = 'Beschrijf het onderwerp van de offline sessie.';
$string['offline_session_ref_course'] = 'Ref.Cursus';
$string['offline_session_ref_course_help'] = 'Selecteer de cursus waaraan in de offline sessie aan gewerkt is.';

// Offline Sessions validations
$string['login_must_be_before_logout'] = 'Starttijd na eindtijd!';
$string['dayscertificable_exceeded'] = 'Maximaal {$a} dagen geleden';
$string['overlaps_old_sessions'] = 'Deze sessie overlapt een huidige sessie';
$string['overlaps_current_session'] = 'Sessie overlapt de huidige online seesie';
$string['unreasoneable_session'] = 'Weet u het zeker? Meer dan {$a} uur!';
$string['logout_is_future'] = 'Mag niet in de toekomst plaatsvinden';

$string['tracked_users'] = 'Bijgehouden cursisten';

// Activity Completion tracking
$string['completiontotalduration'] = 'Benodigde tijd [minuten]';
$string['completiondurationgroup'] = 'Totaal geregistreerde tijd';

$string['eventsessionupdate']= "Sessie update";
$string['eventaddofflinesession']= "Offline sessie toevoegen";
$string['eventdelofflinesession']= "Offline sessie verwijderen";
$string['crontask']='Herbereken sessies';