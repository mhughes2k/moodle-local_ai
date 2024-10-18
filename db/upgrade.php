<?php

function xmldb_local_ai_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024051800) {
        // Define table aiprovider to be created.
        $table = new xmldb_table('local_ai_aiprovider');

        // Adding fields to table aiprovider.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('apikey', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('allowembeddings', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('allowchat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('baseurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('embeddingsurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('completionsurl', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('embeddingmodel', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('completionmodel', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('onlyenrolledcourses', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1');

        // Adding keys to table aiprovider.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for aiprovider.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Recompletion savepoint reached.
        upgrade_plugin_savepoint(true, 2024051800, 'local', 'ai');
    }
    if ($oldversion < 2024051800.1) {

        // Define table local_ai_cmconfig to be created.
        $table = new xmldb_table('local_ai_cmconfig');

        // Adding fields to table local_ai_cmconfig.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('aicontext', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('allowsummarise', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('allowexplain', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('allowask', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('allowanswer', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('allowtranslate', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('aiproviderid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');
        $table->add_field('allowindex', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'aicontext');
        $table->add_field('allowchat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'allowtranslate');

        // Adding keys to table local_ai_cmconfig.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('usermodified', XMLDB_KEY_FOREIGN, ['usermodified'], 'user', ['id']);

        // Conditionally launch create table for local_ai_cmconfig.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Ai savepoint reached.
        upgrade_plugin_savepoint(true, 2024051800.1, 'local', 'ai');
    }
    if ($oldversion < 2024052000.1) {
        // Define table aiprovider to be created.
        $table = new xmldb_table('local_ai_aiprovider');
        $table->add_field('aimaxcontext', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '128000', 'allowchat');
        // Ai savepoint reached.
        upgrade_plugin_savepoint(true, 2024052000.1, 'local', 'ai');
    }

    return true;
}
