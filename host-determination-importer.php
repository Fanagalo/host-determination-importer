<?php
/*
Plugin Name: Host Determination Importer
Description: A basic plugin to import CSV data into a custom MySQL table. Fundament by ChatGPT
Version: 1.0
Author: Jaap Wiering
*/

// Hook to create a new table when the plugin is activated
register_activation_hook(__FILE__, 'create_temporary_table');

function create_temporary_table()
{
    global $wpdb;
    $table_name = 'temporary_table';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        host varchar(255) NOT NULL,
        organ varchar(255),
        mode varchar(255) NOT NULL,
        stage varchar(255) NOT NULL,
        tax_top varchar(255) NOT NULL,
        tax_middle varchar(255) NOT NULL,
        tax_family varchar(255) NOT NULL,
        parasite varchar(255) NOT NULL,
        genera_number varchar(255) NOT NULL,
        species_number varchar(255) NOT NULL,
        parasite_with_image BOOL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Hook to add admin menu page for CSV uploading
add_action('admin_menu', 'host_determination_importer_menu');

function host_determination_importer_menu()
{
    add_menu_page(
        'Host Determination Importer',               // Page title
        'Host Determination Importer',               // Menu title
        'manage_options',             // Capability
        'host-determination-importer',               // Menu slug
        'host_determination_importer_page'           // Function to display page
    );
}

// Admin page content for CSV upload
function host_determination_importer_page()
{
?>
    <div class="wrap">
        <h1>Host Determination Importer</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv">
            <br><br>
            <input type="submit" name="submit_csv" class="button button-primary" value="Import CSV">
        </form>
    </div>
<?php

    // If form is submitted and a CSV file is uploaded
    if (isset($_POST['submit_csv'])) {
        if (!empty($_FILES['csv_file']['tmp_name'])) {
            csv_import_data($_FILES['csv_file']['tmp_name']);
        } else {
            echo '<div class="error"><p>Please upload a CSV file.</p></div>';
        }
    }
}

// Function to process the CSV file and import data into table01
function csv_import_data($csv_file)
{
    global $wpdb;
    $table_name = 'temporary_table';

    // Open the CSV file for reading
    if (($handle = fopen($csv_file, 'r')) !== false) {
        $row = 0;

        // Loop through the CSV rows
        while (($data = fgetcsv($handle, 2000, ',')) !== false) {
        
        // Skip the first row if it contains column headers
            if ($row == 0) {
                $row++;
                continue;
            }

            // Insert data into the table
            $wpdb->insert(

                $table_name,
                array(
                    'host' => sanitize_text_field($data[0]),
                    'organ' => sanitize_text_field($data[1]),
                    'mode' => sanitize_text_field($data[2]),
                    'stage' => sanitize_text_field($data[3]),
                    'tax_top' => sanitize_text_field($data[4]),
                    'tax_middle' => sanitize_text_field($data[5]),
                    'tax_family' => sanitize_text_field($data[6]),
                    'parasite' => sanitize_text_field($data[7]),
                    'genera_number' => sanitize_text_field($data[8]),
                    'species_number' => sanitize_text_field($data[9]),
                )

            );
            $row++;
        }
        fclose($handle);

        echo '<div class="updated"><p>CSV data successfully imported.</p></div>';
    } else {
        echo '<div class="error"><p>Failed to open the CSV file.</p></div>';
    }
}