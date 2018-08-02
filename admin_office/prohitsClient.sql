-- phpMyAdmin SQL Dump
-- version 2.6.0-pl3
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jul 12, 2005 at 01:42 PM
-- Server version: 3.23.41
-- PHP Version: 4.1.1
-- 
-- Database: `prohitsClient`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `LCQ_BackupFiles`
-- 

CREATE TABLE LCQ_BackupFiles (
  ID int(11) NOT NULL auto_increment,
  BackupFolderID int(11) NOT NULL default '0',
  FileName varchar(255) NOT NULL default '',
  WellCode varchar(10) default NULL,
  PRIMARY KEY  (ID),
  KEY BackupFolderID (BackupFolderID)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `LCQ_BackupFolders`
-- 

CREATE TABLE LCQ_BackupFolders (
  ID int(11) NOT NULL auto_increment,
  FolderName varchar(200) NOT NULL default '',
  ProhitsID int(11) NOT NULL default '0',
  PlateType varchar(20) default NULL,
  UserName varchar(20) default NULL,
  Date datetime default NULL,
  PRIMARY KEY  (ID)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `LCQ_ParserConf`
-- 

CREATE TABLE LCQ_ParserConf (
  ID int(11) NOT NULL auto_increment,
  SearchTaskID int(11) NOT NULL default '0',
  TargetDB varchar(20) default NULL,
  MinScore tinyint(4) NOT NULL default '0',
  SaveValidation tinyint(4) NOT NULL default '0',
  ExecuteTime datetime default NULL,
  ParseStatus varchar(10) default NULL,
  ParsedBy varchar(15) default NULL,
  ParseMascotFileStr text,
  SavedMascotFileStr text,
  ParseGpmFileStr text,
  SavedGpmFileStr tinyint(4) default NULL,
  SetDate datetime default NULL,
  MascotValidateOptions text,
  GpmValidateOptoins text,
  PRIMARY KEY  (ID)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `LCQ_SearchResults`
-- 

CREATE TABLE LCQ_SearchResults (
  ID int(11) NOT NULL auto_increment,
  SearchTaskID int(11) NOT NULL default '0',
  FilePath varchar(250) default NULL,
  FileName varchar(100) NOT NULL default '',
  ParentFolderName varchar(100) default NULL,
  MascotURL varchar(255) default NULL,
  GpmURL varchar(255) default NULL,
  BackupFileID int(11) NOT NULL default '0',
  Date date default NULL,
  PRIMARY KEY  (ID),
  KEY SearchTaskID (SearchTaskID)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `LCQ_SearchTasks`
-- 

CREATE TABLE LCQ_SearchTasks (
  ID int(11) NOT NULL auto_increment,
  TaskTitle varchar(255) default NULL,
  MascotSetName varchar(10) default NULL,
  MascotParmSet text,
  GPMSetName varchar(10) default NULL,
  GPMParmSet text,
  FilterType varchar(255) default NULL,
  FilterOptions varchar(255) default NULL,
  Schedule varchar(20) default NULL,
  StartTime varchar(20) default NULL,
  AutoInputFolderPath varchar(200) default NULL,
  TaskStatus varchar(20) default NULL,
  UserName varchar(20) default NULL,
  ComputerName varchar(30) default NULL,
  Date date default NULL,
  PRIMARY KEY  (ID)
) TYPE=MyISAM COMMENT='prohits Client task';
