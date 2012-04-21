-- phpMyAdmin SQL Dump
-- version 2.8.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Feb 04, 2010 at 01:55 AM
-- Server version: 4.1.13
-- PHP Version: 5.1.4
-- 
-- Database: `babynewt`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `Category`
-- 

CREATE TABLE `Category` (
  `i_CategoryId` int(11) NOT NULL auto_increment,
  `vc_Name` varchar(255) default NULL,
  `vc_CSSName` varchar(20) default NULL,
  `t_Description` text NOT NULL,
  PRIMARY KEY  (`i_CategoryId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `Comment`
-- 

CREATE TABLE `Comment` (
  `i_CommentId` int(11) NOT NULL auto_increment,
  `t_Comment` longtext,
  `i_UID` int(11) default NULL,
  `dt_DatePosted` datetime default NULL,
  `i_CategoryId` int(11) NOT NULL default '0',
  PRIMARY KEY  (`i_CommentId`),
  KEY `IX_Comment_Users` (`i_UID`),
  KEY `IX_Comment_Category` (`i_CategoryId`),
  KEY `IX_DatePosted` (`dt_DatePosted`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `CommentArchive1`
-- 

CREATE TABLE `CommentArchive1` (
  `i_CommentId` int(11) NOT NULL auto_increment,
  `t_Comment` longtext,
  `i_UID` int(11) default NULL,
  `dt_DatePosted` datetime default NULL,
  `i_CategoryId` int(11) NOT NULL default '0',
  PRIMARY KEY  (`i_CommentId`),
  KEY `IX_Comment_Users` (`i_UID`),
  KEY `IX_Comment_Category` (`i_CategoryId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `DBStyleSheet`
-- 

CREATE TABLE `DBStyleSheet` (
  `i_StyleSheetId` int(11) default NULL,
  `t_StyleSheet` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `FILEStyleSheet`
-- 

CREATE TABLE `FILEStyleSheet` (
  `i_StyleSheetId` int(11) default NULL,
  `vc_Filename` varchar(255) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `Favorites`
-- 

CREATE TABLE `Favorites` (
  `i_UID` int(11) NOT NULL default '0',
  `i_CommentId` int(11) NOT NULL default '0',
  `dt_timestamp` date NOT NULL default '0000-00-00',
  `vc_Annotation` varchar(255) default NULL,
  KEY `IX_Favorites_UID` (`i_UID`),
  KEY `IX_Favorites_CommentId` (`i_CommentId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `StyleSheet`
-- 

CREATE TABLE `StyleSheet` (
  `i_StyleSheetId` int(11) NOT NULL auto_increment,
  `i_UID` int(11) default NULL,
  `i_StyleSheetTypeId` int(11) default NULL,
  `i_Public` int(11) default NULL,
  `vc_Name` varchar(255) default NULL,
  PRIMARY KEY  (`i_StyleSheetId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `StyleSheetType`
-- 

CREATE TABLE `StyleSheetType` (
  `i_StyleSheetTypeId` int(11) NOT NULL auto_increment,
  `vc_Description` varchar(255) default NULL,
  PRIMARY KEY  (`i_StyleSheetTypeId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `Tagline`
-- 

CREATE TABLE `Tagline` (
  `i_TaglineId` int(11) NOT NULL auto_increment,
  `vc_Tagline` text,
  PRIMARY KEY  (`i_TaglineId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `TaglinePrefix`
-- 

CREATE TABLE `TaglinePrefix` (
  `i_TaglinePrefixId` int(11) NOT NULL auto_increment,
  `vc_TaglinePrefix` varchar(255) default NULL,
  `vc_TaglineSuffix` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`i_TaglinePrefixId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `Template`
-- 

CREATE TABLE `Template` (
  `i_TemplateID` int(11) NOT NULL auto_increment,
  `t_TemplateHdr` text NOT NULL,
  `t_TemplateCmt` text NOT NULL,
  `t_TemplateFtr` text NOT NULL,
  `i_UID` int(11) NOT NULL default '-1',
  `b_Public` tinyint(4) NOT NULL default '0',
  `vc_TemplateName` varchar(100) NOT NULL default 'Nameless',
  PRIMARY KEY  (`i_TemplateID`),
  UNIQUE KEY `i_TemplateID` (`i_TemplateID`),
  KEY `i_TemplateID_2` (`i_TemplateID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `UserStyleSheet`
-- 

CREATE TABLE `UserStyleSheet` (
  `i_UID` int(11) NOT NULL default '0',
  `i_StyleSheetId` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `UserTemplate`
-- 

CREATE TABLE `UserTemplate` (
  `i_UID` int(11) NOT NULL default '0',
  `i_TemplateID` int(11) NOT NULL default '0',
  PRIMARY KEY  (`i_UID`,`i_TemplateID`),
  UNIQUE KEY `i_UID` (`i_UID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `Users`
-- 

CREATE TABLE `Users` (
  `i_UID` int(11) NOT NULL auto_increment,
  `vc_UserId` text NOT NULL,
  `vc_Username` varchar(100) default NULL,
  `vc_Password` varchar(20) default NULL,
  `vc_Email` varchar(255) default NULL,
  `vc_URL` varchar(255) default NULL,
  `i_Status` tinyint(4) default NULL,
  `dt_DateJoined` datetime NOT NULL default '0000-00-00 00:00:00',
  `dt_LastVisit` datetime NOT NULL default '0000-00-00 00:00:00',
  `dt_LastPosted` datetime default '0000-00-00 00:00:00',
  `i_CommentId` int(11) NOT NULL default '0',
  `i_ShareStyles` tinyint(4) NOT NULL default '0',
  `b_PublicEmail` tinyint(4) NOT NULL default '0',
  `vc_GMTOffset` varchar(5) default '0',
  PRIMARY KEY  (`i_UID`),
  KEY `vc_Username` (`vc_Username`),
  KEY `IX_LastVisit` (`dt_LastVisit`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
