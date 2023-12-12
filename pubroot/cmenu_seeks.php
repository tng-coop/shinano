<?php

declare(strict_types=1);

include_once(__DIR__ . "/../lib/common.php");


// deny not loggedin request
if(! $login->user()){
    please_login_page();
    exit();
}

// Ask DB about user's job_entry

$sql1 = <<<SQL1
SELECT id AS eid, attribute, user, title, description, created_at, updated_at, opened_at, closed_at 
  FROM job_entry
  WHERE user = :user
SQL1;

$job_entries_array = db_ask_ro($sql1, [":user" => $login->user('id')]);

// prepare contents

function job_entry_table (array $job_entries_array, $edit_menu_p=false){
    // accessor for array
    $col_keys = ['eid', 'attribute', 'title', 'description', 'created_at', 'updated_at', 'opened_at', 'closed_at'];

    // table
    $tml_text  = "";
    $tml_text .= "<table>";

    // table head
    $tr_keys  = array_merge
              (['id', 'L/S', 'title', 'detail', 'created', 'updated', 'opened', 'closed'],
               ($edit_menu_p ? ['edit', 'delete'] : []));

    $tml_text .= "<tr>"
              . array_reduce($tr_keys, fn($carry, $key) => $carry . " <th>$key</th> ", "")
              . "</ tr>";

    // table rows
    foreach($job_entries_array as $row){
        // each row into html injection safe
        $row_tml_formed = [];
        foreach($col_keys as $key) {
            $row_tml_formed[$key] = h((gettype($row[$key])=='string') ?
                                      mb_strimwidth($row[$key], 0, 50, '...', 'UTF-8') :
                                      $row[$key]);
        }

        // edit menu buttons if edit_menu_p
        if($edit_menu_p){
            $tml_delete_button = "<td>".tml_entry_delete_button($row['eid'])."</td>";
            $tml_edit_button = "<td>".tml_entry_edit_button($row['eid'])."</td>";
        }

        // tml of each row
        $row_tml = "<tr>"
                 . array_reduce($col_keys,
                                fn($carry, $key) => 
                                $carry . "<td>".h($row_tml_formed[$key])."</td>",
                                "")
                 . (($edit_menu_p) ? $tml_edit_button : "")
                 . (($edit_menu_p) ? $tml_delete_button : "")
                 . "</tr>";

        $tml_text .= $row_tml;
    }

    // end of table
    $tml_text .= "</table>";

    // return
    return $tml_text;
}

function tml_entry_delete_button(int $job_entry_id){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    $form_tml = "<form action='cmenu_delete_job_entry.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='entry_id' value='{$job_entry_id}'>"
              . "  <input type='submit' value='delete' />"
              . "</form>";
    return $form_tml;
}

function tml_entry_edit_button(int $job_entry_id){
    global $csrf;
    $token = $csrf->hiddenInputHTML();

    $form_tml = "<form action='cmenu_seek_edit.php' method='POST'>"
              . "  " . $token
              . "  <input type='hidden' name='mode' value='edit_exist_post'>"
              . "  <input type='hidden' name='entry_id' value='{$job_entry_id}'>"
              . "  <input type='submit' value='edit' />"
              . "</form>";
    return $form_tml;
}



// render HTML

$job_entry_table_html = job_entry_table($job_entries_array, true);


$content_job_entries = <<<CONTENT_JOB_ENTRIES
{$job_entry_table_html}
CONTENT_JOB_ENTRIES;

// render HTML by template
RenderByTemplate("template.html", "job_entries - Shinano -",
                 $content_job_entries);


?>
