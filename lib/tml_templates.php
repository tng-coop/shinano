<?php

declare(strict_types=1);

include_once(__DIR__ . '/./utilities.php');
include_once(__DIR__ . '/./transactions.php');
include_once(__DIR__ . '/./csrf.php');

// # wrap Template and Configs

class TemplateAndConfigs{

    // config and config values
    function __construct(){
        global $pubroot;

        $this->_document_root = realpath(__DIR__ . "/../");
        $this->_URL = get_url();
        $this->pub = $pubroot;
    }

    // template
    function eval_template($template_file){
        $v = $this;
        //include($this->_document_root . "/./template/{$template_file}");
        include(__DIR__ . "/./template/{$template_file}");
    }
}

function RenderByTemplate($template_file, $title, $contents,
                                $values = []){

    $tpl = new TemplateAndConfigs();
    $tpl->page_title = $title;
    $tpl->content_actual = $contents;


    /*
    foreach($values as $val){
        // register val to variables of class.
        $name = (varName($val));
        $this->$name = $val;

    }
    */

    // apply and echos template
    $tpl->eval_template($template_file);

}


// URL to specific pages

function url_of_bulletin_detail($job_entry_id){
    // method for detect specific job_entry is going to be changed.
    global $pubroot;
    return "{$pubroot}bulletin.php?eid={$job_entry_id}";
}

function url_of_cooperator_detail($puid){
    global $pubroot;
    return "${pubroot}cooperator.php?puid=${puid}";
}

// specific parts of html

// npages <a href> s

function html_text_of_npages_a_hrefs
    ($script_relative_url, $npage_current, $n_entries, $entries_per_page, 
     $additional_query=null){

    $n_npages = ceil($n_entries / $entries_per_page);
    global $pubroot;
    $script_link = "{$pubroot}{$script_relative_url}";

    $a_s_tml = "";
    for($iter=1; $iter<=$n_npages; $iter++){
        $additional_query_string = $additional_query ? "{$additional_query}&" : "";
        $a_s_tml .= " <a href='{$script_link}?{$additional_query_string}npage={$iter}'>{$iter}</a>";
    }
    return $a_s_tml;
}


// cooperator's html

function html_text_of_cooperator(array $user_info, $omit_p=false, $omitted_length=0){
    // $bulletin_info need to be included both of user info
    $vals = array_map('h', $user_info);

    // content
    $cooperator_url = url_of_cooperator_detail($vals['public_uid']);

    $h_n_tml
        = ((! $omit_p)
           ? "<h3>{$vals['name']}</h3>"
           : "<a href='{$cooperator_url}'><h3>{$vals['name']}</h3></a>");
    $note_omitted 
        = ((! $omit_p)
           ? $vals['note']
           : mb_strimwidth($vals['note'], 0, $omitted_length, '...', 'UTF-8'));
    $detail_a_href
        = ((! $omit_p) ? "" : "  <a href='{$cooperator_url}'>(detail)</a>");

    $tml_text
        = "<div class='cooperator'>"
        . $h_n_tml
        . "  <ul class='posten_meta'>"
        . "    <li>created: {$vals['created_at']}</li>"
        . "    <li>email: {$vals['email']}</li>"
        . "    <li>public_uid: {$vals['public_uid']}</li>"
        . "  </ul>"
        . "  <p class='posten_content'>{$note_omitted}"
        . $detail_a_href
        . "  </p>"
        . "</div>";

    return $tml_text;
}

// bulletin's html

function html_text_of_bulletin(array $bulletin_info, $omit_p=false, $omitted_length=0){
    // $bulletin_info need to be included both of user and job_entry informations
    $vals = array_map('h', $bulletin_info);

    // content
    $listing_or_seeking = ($vals['attribute'] =='L'  ?  'Listing' :
                           ($vals['attribute']=='S' ?  'Seeking' : 'showing'));
    $detail_url = url_of_bulletin_detail($vals['id']); // id of job_entry.id
    $cooperator_url = url_of_cooperator_detail($vals['public_uid']);

    $h_n_tml
        = ((! $omit_p)
           ? "<h3>{$vals['title']}</h3>"
           : "<a href='{$detail_url}'><h3>{$vals['title']}</h3></a>");
    $description_omitted 
        = ((! $omit_p)
           ? $vals['description']
           : mb_strimwidth($vals['description'], 0, $omitted_length, '...', 'UTF-8'));
    $detail_a_href
        = ((! $omit_p) ? "" : "  <a href='{$detail_url}'>(detail)</a>");

    
    $tml_text 
           = "<div class='bulletin'>"
           . $h_n_tml
           . "  {$listing_or_seeking} by "
           . "  <a href='{$cooperator_url}'>{$vals['name']}</a> <br />"
           . "  <ul class='posten_meta'>"
           . "    <li>eid: {$vals['id']}</li>"
           . "    <li>S/L: {$vals['attribute']} </li>"
           . "    <li>created: {$vals['created_at']}</li>"
           . "    <li>updated: {$vals['updated_at']}</li>"
           . "  </ul>"
           . "  <p class='posten_content'> {$description_omitted}"
           . $detail_a_href 
           . "  </p>"
           . "</div>";
    return $tml_text;
}


function tml_bulletin_edit_button(int $job_entry_id){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    global $pubroot;
    $form_tml = "<form action='{$pubroot}cmenu/bulletin_edit.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='step_demand' value='ask_db_edit_post'>"
              . "  <input type='hidden' name='job_entry_id' value='{$job_entry_id}'>"
              . "  <input type='submit' value='edit' />"
              . "</form>";
    return $form_tml;
}

function tml_bulletin_swap_open_close_button(int $job_entry_id, $opened_at, $closed_at){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    global $pubroot;
    [$demand, $demand_text] = (job_entry_opened_p($opened_at, $closed_at) 
                               ? ['let_close', 'Close it']
                               : ['let_open',  'Open it']);

    $form_tml = "<form action='{$pubroot}cmenu/bulletin_swap_open_close.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='entry_id' value='{$job_entry_id}'>"
              . "  <button type='submit' name='demand' value='{$demand}'>{$demand_text}</button>"
              . "</form>";
    return $form_tml;
}

function html_text_of_bulletins_table (array $bulletin_array, $edit_menu_p=false){
    // accessor for array
    $col_keys = ['eid', 'attribute', 'title', 'description', 'created_at', 'updated_at', 'opened_at', 'closed_at'];

    // table
    $tml_text  = "";
    $tml_text .= "L/S means Listing or Seeking. O/C means Opened or Closed.";
    $tml_text .= "<table>";

    // table head
    $tr_keys  = array_merge
              (['id', 'L/S', 'title', 'A', 'detail', 'created', 'updated'],
               ($edit_menu_p ? ['O/C', 'swap O/C', 'edit'] : [])
              ); // opened_at or closed_at is equally to updated.
    $tml_text .= "<tr>"
              .  array_reduce($tr_keys, fn($carry, $key) => $carry . " <th>$key</th> ", "")
              .  "</ tr>";

    // hide not-opened entries if-not editmenu.
    if(! $edit_menu_p){
        $bulletin_array
            = array_filter($bulletin_array,
                           fn($row) => job_entry_opened_p($row['opened_at'], $row['closed_at']));
    }
    

    // table rows
    foreach($bulletin_array as $row){
        // each row into html injection safe
        $row_tml_formed = [];
        foreach($col_keys as $key) {
            $row_tml_formed[$key] = h(mb_strimwidth((string)$row[$key], 0, 50, '...', 'UTF-8'));
        }

        //
        $row_tml_formed['a_href'] = "<a href='".url_of_bulletin_detail($row['eid'])."'>A</a>";
        $row_tml_formed['open_close'] = job_entry_opened_p($row['opened_at'], $row['closed_at']) ? 'O' : 'C';

        // edit menu buttons if edit_menu_p
        if($edit_menu_p){
            $tml_swap_open_close_button
                = "<td>"
                . tml_bulletin_swap_open_close_button($row['eid'],$row['opened_at'],$row['closed_at'])
                . "</td>";
            $tml_edit_button = "<td>".tml_bulletin_edit_button($row['eid'])."</td>";
        }

        // tml of each row
        $row_tml = "<tr>"
                 // show table
                 . "<td>" . $row_tml_formed['eid'] . "</td>"
                 . "<td>" . $row_tml_formed['attribute'] . "</td>"
                 . "<td>" . $row_tml_formed['title'] . "</td>"
                 . "<td>" . $row_tml_formed['a_href'] . "</td>"
                 . "<td>" . $row_tml_formed['description'] . "</td>"
                 . "<td>" . $row_tml_formed['created_at'] . "</td>"
                 . "<td>" . $row_tml_formed['updated_at'] . "</td>"
                 // for edit menu
                 . ($edit_menu_p
                    ? (""
                       . "<td>" . $row_tml_formed['open_close'] . "</td>"
                       . $tml_swap_open_close_button
                       . $tml_edit_button)
                    : "")
                 . "</tr>";

        $tml_text .= $row_tml;
    }

    // end of table
    $tml_text .= "</table>";

    // return
    return $tml_text;
}






?>
