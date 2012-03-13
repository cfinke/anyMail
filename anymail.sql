CREATE TABLE IF NOT EXISTS `anymail_attachment_data` (
  `data_id` int(11) NOT NULL default '0',
  `part_id` int(11) NOT NULL default '0',
  `data` longtext NOT NULL
) ;

-- --------------------------------------------------------

-- 
-- Table structure for table `anymail_attachments`
-- 

CREATE TABLE IF NOT EXISTS `anymail_attachments` (
  `attachment_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `filename` varchar(64) NOT NULL default '',
  `mime_type` varchar(32) NOT NULL default '',
  `encoding` varchar(32) NOT NULL default '',
  `data_id` int(11) NOT NULL default '0',
  `hash` varchar(64) NOT NULL default '',
  UNIQUE KEY `attachment_id` (`attachment_id`)
) ;

-- --------------------------------------------------------

-- 
-- Table structure for table `anymail_contact_groups`
-- 

CREATE TABLE IF NOT EXISTS `anymail_contact_groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) NOT NULL default '',
  `contact_ids` text NOT NULL,
  `user_id` int(11) NOT NULL default '0',
  UNIQUE KEY `contact_group` (`group_id`)
) ;

-- --------------------------------------------------------

-- 
-- Table structure for table `anymail_contacts`
-- 

CREATE TABLE IF NOT EXISTS `anymail_contacts` (
  `contact_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `contact_name` varchar(255) NOT NULL default '',
  `contact_email` varchar(255) NOT NULL default '',
  UNIQUE KEY `contact_id` (`contact_id`)
) ;

-- --------------------------------------------------------

-- 
-- Table structure for table `anymail_hosts`
-- 

CREATE TABLE IF NOT EXISTS `anymail_hosts` (
  `host_id` int(11) NOT NULL auto_increment,
  `domain` varchar(255) NOT NULL default '',
  `protocol` varchar(8) NOT NULL default '',
  `port` varchar(8) NOT NULL default '',
  UNIQUE KEY `host_id` (`host_id`)
) ;

-- --------------------------------------------------------

-- 
-- Table structure for table `anymail_labels`
-- 

CREATE TABLE IF NOT EXISTS `anymail_labels` (
  `label_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `label_name` varchar(64) NOT NULL default '',
  UNIQUE KEY `label_id` (`label_id`),
  KEY `user_id` (`user_id`,`label_name`)
) ;

-- --------------------------------------------------------

-- 
-- Table structure for table `anymail_messages`
-- 

CREATE TABLE IF NOT EXISTS `anymail_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `headers` text NOT NULL,
  `attachments` text NOT NULL,
  `Return-Path` varchar(255) NOT NULL default '',
  `From` varchar(255) NOT NULL default '',
  `Reply-To` varchar(255) NOT NULL default '',
  `To` text NOT NULL,
  `Subject` varchar(255) NOT NULL default '',
  `Cc` text NOT NULL,
  `Message-ID` varchar(255) NOT NULL default '',
  `In-Reply-To` varchar(255) NOT NULL default '',
  `Date` varchar(32) NOT NULL default '',
  `nice_date` varchar(14) NOT NULL default '',
  `labels` text NOT NULL,
  `text_part` longtext NOT NULL,
  `html_part` longtext NOT NULL,
  `seen` tinyint(1) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `sent` tinyint(1) NOT NULL default '0',
  `archived` int(11) NOT NULL default '0',
  UNIQUE KEY `message_id` (`message_id`)
) ;

-- --------------------------------------------------------

-- 
-- Table structure for table `anymail_users`
-- 

CREATE TABLE IF NOT EXISTS `anymail_users` (
  `user_id` int(11) NOT NULL auto_increment,
  `email_address` varchar(255) NOT NULL default '',
  `host_id` int(11) NOT NULL default '0',
  `username` varchar(255) NOT NULL default '',
  UNIQUE KEY `user_id` (`user_id`)
) ;
