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

/**
* Strings for component 'ai', language 'en', branch 'MOODLE_0_STABLE'
*
* @package   core_ai
* @copyright 2024 onwards Michael Hughes {@link http://moodle.com}
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
$string['pluginname'] = 'AI Providers';
$string['aiprovider'] = 'AI Provider';
$string['aiprovidersin'] = 'AI Providers in {$a}';
$string['"ai:addprovider'] = 'Add an AI provider';
$string['ai:manageproviders'] = 'Manage AI Providers';
$string['ai:moveprovider'] = 'Move AI Provider';
$string['ai:selectcategory'] = 'Select AI Provider Category';

$string['addprovider'] = 'Add AI Provider';
$string['anyusercourse'] = 'Any course user is enrolled in';
$string['anywhere'] = 'Anywhere in site';
$string['baseurl_help'] = 'Common shared base URL *without* trailing slash';
$string['enabled'] = 'Enabled';
$string['enabled_help'] = 'Show this AI Provider be available to users.';
$string['extractorpath'] = 'URL to extractor process';
$string['extractorpath_desc'] = 'URL to extractor process';
$string['disabled'] = 'Disabled';
$string['general'] = 'General Settings';
$string['removeprovider'] = 'Remove AI Provider';
$string['manageproviders'] = 'Manage AI Providers';
$string['availableproviders'] = 'Available Providers';
$string['availableproviders_help'] = 'This is a list of configured AI Providers that meet the requirements of the activity.

Activity developers specify which AI features their plugin requires, and an administrator must configure
an AI Provider instance, and make it available at a context level that the plugin can access.

System level AI Providers that meet the required features will always be listed.
';
$string['chat'] = 'Chat Completion';
$string['chat_help'] = 'Chat Completion allows the AIProvider to be used to generate text.';
$string['disable'] = 'Disable';
$string['embedding'] = 'Embedding';
$string['embedding_help'] = 'Embedding allows the AI to generate vector representations of text.';
$string['selectcategory'] = '';
$string['selectcategory_help'] = '';

$string['aiproviderid'] = 'AI Provider';
$string['aiproviderid_help'] = 'Select the AI Provider to use for this activity.';
$string['aiproviderfeatures'] = '';
$string['aiproviderfeatures_desc'] = 'This plugin needs the following AI features';

// providers
$string['providers'] = 'Providers';
$string['newprovider'] = '{$a} Based Provider';

// Provider instance form
$string['providername'] = "Name";
$string['providername_help'] = "Name";
$string['baseurl'] = 'Base URL';
$string['baseurl_help'] = 'Base URL';
$string['apikey'] = 'API Key';
$string['apikey_help'] = 'API KEY ';

$string['features'] = 'Features';
// Plcaements
$string['aicontext'] = 'Activity Context';
$string['aicontext_help'] = 'This is extra context that will be provided to the AI to guide it\'s response';

$string['allowchat'] = 'Allow Chat';
$string['allowchat_help'] = 'Allow this provider to provide chat completion.';

$string['allowindex'] = 'Allow Indexing for AI';
$string['allowindex_help'] = 'Can this resource be indexed and included in AI response.
Disabling this will prevent the AI from using this resource in responses.

Currently yf this has been previously disabled, this resource will not be removed from the 
AI Index but won\'t be indexed in the future. 
';
$string['allowanswer'] = 'Answer Questions about the provided text';
$string['allowanswer_help'] = 'Can this resource be used to answer questions?';
$string['allowsummarise'] = 'Allow Summarise';
$string['allowsummarise_help'] = 'Allow users to us summarise placement on this resource.';
$string['allowexplain'] = 'Allow Explain';
$string['allowexplain_help'] = 'User can us Explain placements with this resource.';
$string['allowask'] = 'Allow Ask Question';
$string['allowask_help'] = 'Can users use AI to geneate questions from this resource?';
$string['allowtranslate'] = 'Allow Translate';
$string['allowtranslate'] = 'Allow Translation';
$string['allowtranslate_help'] = 'Allows this resource to be translated by AI';
$string['allowimagegeneration'] = '';
$string['allowimagegeneration_help'] = '';
$string['allowchat'] = 'Allow Chat';
$string['allowchat_help'] = 'Allow ths resource to be used in chats.';

$string['completionspath'] = 'Completions path';
$string['completionspath_help'] = 'Completions path';
$string['completionmodel'] = 'Completion Model';
$string['completionmodel_help'] = 'Completion Model';
$string['allowembeddings'] = 'Allow Embeddings';
$string['allowembeddings_help'] = 'Allow this reseource to be used to create embeddings.';
$string['embeddingspath'] = 'Embeddings path';
$string['embeddingspath_help'] = 'Embeddings path';
$string['embeddingmodel'] = 'Embedding Model';
$string['embeddingmodel_help'] = 'Embedding Model';
$string['providernotavailable'] = 'Provider {$a} not found, please check configuration';
$string['scopecoursecategory'] = 'Category';
$string['scopecoursecategory_help'] = 'Limit AI scope to courses and sub-categories.

This can be limited to work only against the user\'s enrolled courses.

Users must hold the `moodle/ai:selectcategory` capability on a category to choose it.';
$string['scopecourse'] = 'Course(s)';
$string['scopecourse_help'] = 'Limit AI scope to specific courses.

Not available if a category scope constraint has been chosen.

Users must hold the `moodle/ai:selectcourse` capability on a course to choose it.';

$string['actions'] = 'Actions';
$string['actions_help'] = 'Actions';
$string['contentconstraints'] = 'Content Constraints';
$string['savechanges'] = 'Save changes';
$string['aisettings'] = 'Settings';

$string['systemprompt'] = 'System Prompt';
$string['systemprompt_help'] = 'System Prompt';
$string['defaultsystemprompt'] = 'You are a help Ai that supports University students.';
