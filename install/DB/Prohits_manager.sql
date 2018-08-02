-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: xtandem2.mshri.on.ca
-- Generation Time: Feb 22, 2018 at 12:43 PM
-- Server version: 5.1.73
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `Prohits_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `GeneLevelParse`
--

CREATE TABLE IF NOT EXISTS `GeneLevelParse` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Machine` varchar(150) NOT NULL,
  `TaskID` int(10) NOT NULL,
  `TppID` int(10) NOT NULL,
  `pepXML_original` text NOT NULL,
  `pepXML` text NOT NULL,
  `pepXML_result` text,
  `ProhitsID` int(10) NOT NULL,
  `SearchEngine` varchar(100) NOT NULL,
  `ProjectID` int(10) NOT NULL,
  `isUploaded` int(1) NOT NULL,
  `FastaFile` text NOT NULL,
  `Parsed` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Parsed` (`Parsed`),
  KEY `TppID` (`TppID`),
  KEY `Machine` (`Machine`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

--
-- Dumping data for table `GeneLevelParse`
--

INSERT INTO `GeneLevelParse` (`ID`, `Machine`, `TaskID`, `TppID`, `pepXML_original`, `pepXML`, `pepXML_result`, `ProhitsID`, `SearchEngine`, `ProjectID`, `isUploaded`, `FastaFile`, `Parsed`) VALUES
(1, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/3_30_TPK2_mascot.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/3_task1_tpp1_3_30_TPK2_mascot.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/3_task1_tpp1_3_30_TPK2_mascot.pep.inter.xml_geneResults.txt', 30, 'Mascot', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(2, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/4_201_AVO1_HA_mascot.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/4_task1_tpp1_4_201_AVO1_HA_mascot.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/4_task1_tpp1_4_201_AVO1_HA_mascot.pep.inter.xml_geneResults.txt', 201, 'Mascot', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(3, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/5_32_ALK2_mascot.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/5_task1_tpp1_5_32_ALK2_mascot.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/5_task1_tpp1_5_32_ALK2_mascot.pep.inter.xml_geneResults.txt', 32, 'Mascot', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(4, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/6_202_TPK2_mascot.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/6_task1_tpp1_6_202_TPK2_mascot.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/6_task1_tpp1_6_202_TPK2_mascot.pep.inter.xml_geneResults.txt', 202, 'Mascot', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(5, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/7_200_NET1_HA_mascot.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/7_task1_tpp1_7_200_NET1_HA_mascot.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/7_task1_tpp1_7_200_NET1_HA_mascot.pep.inter.xml_geneResults.txt', 200, 'Mascot', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(6, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/3_30_TPK2_comet.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/3_task1_tpp1_3_30_TPK2_comet.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/3_task1_tpp1_3_30_TPK2_comet.pep.inter.xml_geneResults.txt', 30, 'COMET', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(7, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/4_201_AVO1_HA_comet.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/4_task1_tpp1_4_201_AVO1_HA_comet.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/4_task1_tpp1_4_201_AVO1_HA_comet.pep.inter.xml_geneResults.txt', 201, 'COMET', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(8, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/5_32_ALK2_comet.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/5_task1_tpp1_5_32_ALK2_comet.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/5_task1_tpp1_5_32_ALK2_comet.pep.inter.xml_geneResults.txt', 32, 'COMET', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(9, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/6_202_TPK2_comet.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/6_task1_tpp1_6_202_TPK2_comet.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/6_task1_tpp1_6_202_TPK2_comet.pep.inter.xml_geneResults.txt', 202, 'COMET', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(10, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/7_200_NET1_HA_comet.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/7_task1_tpp1_7_200_NET1_HA_comet.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/7_task1_tpp1_7_200_NET1_HA_comet.pep.inter.xml_geneResults.txt', 200, 'COMET', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(11, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/3_30_TPK2_combined.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/3_task1_tpp1_3_30_TPK2_combined.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/3_task1_tpp1_3_30_TPK2_combined.pep.inter.xml_geneResults.txt', 30, 'iProphet', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(12, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/4_201_AVO1_HA_combined.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/4_task1_tpp1_4_201_AVO1_HA_combined.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/4_task1_tpp1_4_201_AVO1_HA_combined.pep.inter.xml_geneResults.txt', 201, 'iProphet', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(13, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/5_32_ALK2_combined.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/5_task1_tpp1_5_32_ALK2_combined.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/5_task1_tpp1_5_32_ALK2_combined.pep.inter.xml_geneResults.txt', 32, 'iProphet', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(14, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/6_202_TPK2_combined.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/6_task1_tpp1_6_202_TPK2_combined.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/6_task1_tpp1_6_202_TPK2_combined.pep.inter.xml_geneResults.txt', 202, 'iProphet', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(15, 'LTQ_DEMO', 1, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/7_200_NET1_HA_combined.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/7_task1_tpp1_7_200_NET1_HA_combined.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task1/tpp1/7_task1_tpp1_7_200_NET1_HA_combined.pep.inter.xml_geneResults.txt', 200, 'iProphet', 2, 0, '../../TMP/parser/LTQ_DEMO/YEAST_RefV57_cRAPandREVgene_20130129.fasta', 1),
(16, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/8_8_MEPCE_mascot.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/8_task2_tpp2_8_8_MEPCE_mascot.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/8_task2_tpp2_8_8_MEPCE_mascot.pep.inter.xml_geneResults.txt', 8, 'Mascot', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(17, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/9_14_RAF1_mascot.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/9_task2_tpp2_9_14_RAF1_mascot.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/9_task2_tpp2_9_14_RAF1_mascot.pep.inter.xml_geneResults.txt', 14, 'Mascot', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(18, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/10_12_WASL_mascot.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/10_task2_tpp2_10_12_WASL_mascot.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/10_task2_tpp2_10_12_WASL_mascot.pep.inter.xml_geneResults.txt', 12, 'Mascot', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(19, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/11_16_FLAG_Alone_mascot.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/11_task2_tpp2_11_16_FLAG_Alone_mascot.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/11_task2_tpp2_11_16_FLAG_Alone_mascot.pep.inter.xml_geneResults.txt', 16, 'Mascot', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(20, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/8_8_MEPCE_comet.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/8_task2_tpp2_8_8_MEPCE_comet.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/8_task2_tpp2_8_8_MEPCE_comet.pep.inter.xml_geneResults.txt', 8, 'COMET', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(21, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/9_14_RAF1_comet.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/9_task2_tpp2_9_14_RAF1_comet.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/9_task2_tpp2_9_14_RAF1_comet.pep.inter.xml_geneResults.txt', 14, 'COMET', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(22, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/10_12_WASL_comet.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/10_task2_tpp2_10_12_WASL_comet.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/10_task2_tpp2_10_12_WASL_comet.pep.inter.xml_geneResults.txt', 12, 'COMET', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(23, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/11_16_FLAG_Alone_comet.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/11_task2_tpp2_11_16_FLAG_Alone_comet.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/11_task2_tpp2_11_16_FLAG_Alone_comet.pep.inter.xml_geneResults.txt', 16, 'COMET', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(24, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/8_8_MEPCE_combined.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/8_task2_tpp2_8_8_MEPCE_combined.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/8_task2_tpp2_8_8_MEPCE_combined.pep.inter.xml_geneResults.txt', 8, 'iProphet', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(25, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/9_14_RAF1_combined.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/9_task2_tpp2_9_14_RAF1_combined.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/9_task2_tpp2_9_14_RAF1_combined.pep.inter.xml_geneResults.txt', 14, 'iProphet', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(26, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/10_12_WASL_combined.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/10_task2_tpp2_10_12_WASL_combined.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/10_task2_tpp2_10_12_WASL_combined.pep.inter.xml_geneResults.txt', 12, 'iProphet', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(27, 'LTQ_DEMO', 2, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/11_16_FLAG_Alone_combined.pep.inter.xml', '../../TMP/parser/LTQ_DEMO/11_task2_tpp2_11_16_FLAG_Alone_combined.pep.inter.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task2/tpp2/11_task2_tpp2_11_16_FLAG_Alone_combined.pep.inter.xml_geneResults.txt', 16, 'iProphet', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(28, 'LTQ_DEMO', 5, 4, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/tpp4/interact-45_Swath_EIF4aJune7-Biorep2_Q3.pep.xml', '../../TMP/parser/LTQ_DEMO/45_task5_tpp4_interact-45_Swath_EIF4aJune7-Biorep2_Q3.pep.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task5/tpp4/45_task5_tpp4_interact-45_Swath_EIF4aJune7-Biorep2_Q3.pep.xml_geneResults.txt', 204, 'iProphet', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(29, 'LTQ_DEMO', 5, 4, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/tpp4/interact-46_Swath_MEPCEJune7-Biorep3_Q3.pep.xml', '../../TMP/parser/LTQ_DEMO/46_task5_tpp4_interact-46_Swath_MEPCEJune7-Biorep3_Q3.pep.xml', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task5/tpp4/46_task5_tpp4_interact-46_Swath_MEPCEJune7-Biorep3_Q3.pep.xml_geneResults.txt', 203, 'iProphet', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57_cRAPandREVgene_20130130.fasta', 1),
(30, 'LTQ_DEMO', 3, 0, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task3/Results/45_Swath_EIF4aJune7-Biorep2MSPLITfiltered.txt', '', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task3/Results/45_Swath_EIF4aJune7-Biorep2MSPLITfiltered.txt_geneResults.txt', 204, 'MSPLIT', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57cRAPg.fasta', 1),
(31, 'LTQ_DEMO', 3, 0, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task3/Results/46_Swath_MEPCEJune7-Biorep3MSPLITfiltered.txt', '', '/var/www/html/Prohits/ProhitsStorage/Prohits_Data/gene_parse/LTQ_DEMO/task3/Results/46_Swath_MEPCEJune7-Biorep3MSPLITfiltered.txt_geneResults.txt', 203, 'MSPLIT', 3, 0, '../../TMP/parser/LTQ_DEMO/HUMAN_RefV57cRAPg.fasta', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Log`
--

CREATE TABLE IF NOT EXISTS `Log` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) DEFAULT NULL,
  `MyTable` varchar(25) DEFAULT NULL,
  `RecordID` int(11) DEFAULT NULL,
  `MyAction` varchar(25) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `ProjectID` int(11) DEFAULT NULL,
  `TS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `LTQ_DEMO`
--

CREATE TABLE IF NOT EXISTS `LTQ_DEMO` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `FileName` varchar(255) NOT NULL DEFAULT '',
  `FileType` varchar(10) DEFAULT NULL,
  `FolderID` int(11) DEFAULT '0',
  `Date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `User` varchar(20) DEFAULT NULL,
  `ProhitsID` int(11) DEFAULT NULL,
  `ProjectID` int(11) DEFAULT NULL,
  `Size` bigint(12) DEFAULT NULL,
  `ConvertParameter` varchar(255) DEFAULT NULL,
  `RAW_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`),
  KEY `FolderID` (`FolderID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=54 ;

--
-- Dumping data for table `LTQ_DEMO`
--

INSERT INTO `LTQ_DEMO` (`ID`, `FileName`, `FileType`, `FolderID`, `Date`, `User`, `ProhitsID`, `ProjectID`, `Size`, `ConvertParameter`, `RAW_ID`) VALUES
(1, 'YEAST_P2', 'dir', 0, '2015-11-23 16:59:13', '', 0, 2, 418076, NULL, NULL),
(2, 'Human_P3', 'dir', 0, '2015-11-23 17:17:52', '', 0, 3, 6172084, NULL, NULL),
(3, '30_TPK2.RAW', 'RAW', 1, '2015-11-23 16:34:23', '', 30, 2, 85949431, NULL, NULL),
(4, '201_AVO1_HA.RAW', 'RAW', 1, '2015-11-23 16:44:37', '', 201, 2, 83787877, NULL, NULL),
(5, '32_ALK2.RAW', 'RAW', 1, '2015-11-23 15:43:02', '', 32, 2, 92232215, NULL, NULL),
(6, '202_TPK2.RAW', 'RAW', 1, '2015-11-23 16:36:17', '', 202, 2, 78843100, NULL, NULL),
(7, '200_NET1_HA.RAW', 'RAW', 1, '2015-11-23 16:44:21', '', 200, 2, 87282428, NULL, NULL),
(8, '8_MEPCE.RAW', 'RAW', 2, '2015-11-23 17:25:39', '', 8, 3, 98904605, NULL, NULL),
(9, '14_RAF1.RAW', 'RAW', 2, '2015-11-23 17:24:17', '', 14, 3, 64406996, NULL, NULL),
(10, '12_WASL.RAW', 'RAW', 2, '2015-11-23 17:25:06', '', 12, 3, 144586734, NULL, NULL),
(11, '16_FLAG_Alone.RAW', 'RAW', 2, '2015-11-23 17:22:19', '', 16, 3, 138382297, NULL, NULL),
(12, '30_TPK2.mgf', 'mgf', 1, '2015-11-23 17:34:22', '', 30, 2, 168224178, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 3),
(13, '30_TPK2.mzML', 'mzML', 1, '2015-11-23 17:34:24', '', 30, 2, 132867108, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 3),
(14, '8_MEPCE.mgf', 'mgf', 2, '2015-11-23 17:44:02', '', 8, 3, 206353054, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 8),
(15, '8_MEPCE.mzML', 'mzML', 2, '2015-11-23 17:44:04', '', 8, 3, 142140277, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 8),
(16, '201_AVO1_HA.mgf', 'mgf', 1, '2015-11-23 17:45:35', '', 201, 2, 157837580, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 4),
(17, '201_AVO1_HA.mzML', 'mzML', 1, '2015-11-23 17:45:37', '', 201, 2, 131756944, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 4),
(18, '32_ALK2.mgf', 'mgf', 1, '2015-11-23 17:58:53', '', 32, 2, 178138295, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 5),
(19, '32_ALK2.mzML', 'mzML', 1, '2015-11-23 17:58:55', '', 32, 2, 136169909, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 5),
(20, '14_RAF1.mzML', 'mzML', 2, '2015-11-23 18:09:00', '', 14, 3, 100680493, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 9),
(21, '14_RAF1.mgf', 'mgf', 2, '2015-11-23 18:09:01', '', 14, 3, 101983725, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 9),
(22, '202_TPK2.mgf', 'mgf', 1, '2015-11-23 18:12:16', '', 202, 2, 142051452, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 6),
(23, '202_TPK2.mzML', 'mzML', 1, '2015-11-23 18:12:18', '', 202, 2, 118155233, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 6),
(24, '200_NET1_HA.mgf', 'mgf', 1, '2015-11-23 18:24:00', '', 200, 2, 165490760, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 7),
(25, '200_NET1_HA.mzML', 'mzML', 1, '2015-11-23 18:24:02', '', 200, 2, 136512659, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 7),
(26, '12_WASL.mzML', 'mzML', 2, '2015-11-23 18:28:29', '', 12, 3, 212585352, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 10),
(27, '12_WASL.mgf', 'mgf', 2, '2015-11-23 18:28:33', '', 12, 3, 275646395, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 10),
(28, '16_FLAG_Alone.mzML', 'mzML', 2, '2015-11-23 19:03:46', '', 16, 3, 193462053, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 11),
(29, '16_FLAG_Alone.mgf', 'mgf', 2, '2015-11-23 19:03:50', '', 16, 3, 245786210, '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', 11),
(42, 'Swath_EIF4aJune7-Biorep2.wiff.scan', 'scan', 2, '2015-11-24 14:11:07', '', 0, 3, 1708901348, NULL, NULL),
(43, 'Swath_MEPCEJune7-Biorep3.wiff.scan', 'scan', 2, '2015-11-24 13:45:13', '', 0, 3, 1620586024, NULL, NULL),
(44, 'Swath_EIF4aJune7-Biorep2.mzXML.gz', 'mzXML.gz', 2, '2015-11-26 12:36:06', '', 0, 3, 1308585933, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 45),
(45, 'Swath_EIF4aJune7-Biorep2.wiff', 'wiff', 2, '2015-11-24 14:14:43', '1', 204, 3, 9060352, NULL, NULL),
(46, 'Swath_MEPCEJune7-Biorep3.wiff', 'wiff', 2, '2015-11-24 13:58:59', '1', 203, 3, 9060352, NULL, NULL),
(47, 'Swath_MEPCEJune7-Biorep3.mzXML.gz', 'mzXML.gz', 2, '2015-11-26 14:52:22', '', 0, 3, 1217707016, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 46),
(48, 'Swath_EIF4aJune7-Biorep2.mzML.gz', 'mzML.gz', 2, '2015-11-24 16:10:28', '1', 204, 3, 1212735620, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 45),
(49, 'Swath_EIF4aJune7-Biorep2.mgf', 'mgf', 2, '2015-11-24 16:11:50', '1', 204, 3, 4084653710, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 45),
(50, 'Swath_MEPCEJune7-Biorep3.mzML.gz', 'mzML.gz', 2, '2015-11-24 17:25:59', '1', 203, 3, 1128088198, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 46),
(51, 'Swath_MEPCEJune7-Biorep3.mgf', 'mgf', 2, '2015-11-24 17:27:09', '1', 203, 3, 3782704975, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 46),
(52, 'Swath_EIF4aJune7-Biorep2.mzML', 'mzML', 2, '2015-11-26 12:36:45', '1', 204, 3, 1808811959, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 45),
(53, 'Swath_MEPCEJune7-Biorep3.mzML', 'mzML', 2, '2015-11-26 14:52:44', '1', 203, 3, 1699564893, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 46);

-- --------------------------------------------------------

--
-- Table structure for table `LTQ_DEMOSaveConf`
--

CREATE TABLE IF NOT EXISTS `LTQ_DEMOSaveConf` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TaskID` int(11) NOT NULL DEFAULT '0',
  `Mascot_SaveScore` varchar(30) DEFAULT NULL,
  `Mascot_SaveValidation` tinyint(4) DEFAULT '0',
  `Status` varchar(30) DEFAULT NULL,
  `SaveBy` varchar(25) DEFAULT NULL,
  `SetDate` datetime DEFAULT NULL,
  `Mascot_SaveWell_str` text,
  `GPM_SaveWell_str` text,
  `Mascot_Other_Value` text,
  `GPM_Value` varchar(200) DEFAULT NULL,
  `TppTaskID` int(11) DEFAULT NULL,
  `Tpp_SaveWell_str` text,
  `Tpp_Value` varchar(255) DEFAULT NULL,
  `SEQUEST_SaveWell_str` text,
  `SEQUEST_Value` varchar(200) DEFAULT NULL,
  `DECOY_prefix` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `LTQ_DEMOSaveConf`
--

INSERT INTO `LTQ_DEMOSaveConf` (`ID`, `TaskID`, `Mascot_SaveScore`, `Mascot_SaveValidation`, `Status`, `SaveBy`, `SetDate`, `Mascot_SaveWell_str`, `GPM_SaveWell_str`, `Mascot_Other_Value`, `GPM_Value`, `TppTaskID`, `Tpp_SaveWell_str`, `Tpp_Value`, `SEQUEST_SaveWell_str`, `SEQUEST_Value`, `DECOY_prefix`) VALUES
(1, 1, '1', 0, 'Completed', 'Prohits Administrator', '2015-11-24 12:24:22', '3;4;5;6;7', '', ';peptide_min_score:27;requireBoldRed:1;sigthreshold:0.05;report:AUTO;_mudpit:1;sequest_rank:2', 'proex=0,pepex=100,proex_dot=0,pepex_dot=0', 1, 'Mascot:3;4;5;6;7COMET:3;4;5;6;7iProphet:3;4;5;6;7', 'frm_TPP_PARSE_MIN_PROBABILITY=0.05;frm_geneLevelFDR=0.01;frm_pepPROBABILITY=0.85', '', 'sequest_rank=2', NULL),
(2, 2, '1', 0, 'Completed', 'Prohits Administrator', '2015-11-24 12:24:53', '8;9;10;11', '', ';peptide_min_score:27;requireBoldRed:1;sigthreshold:0.05;report:AUTO;_mudpit:1;sequest_rank:2', 'proex=0,pepex=100,proex_dot=0,pepex_dot=0', 2, 'Mascot:8;9;10;11COMET:8;9;10;11iProphet:8;9;10;11', 'frm_TPP_PARSE_MIN_PROBABILITY=0.05;frm_geneLevelFDR=0.01;frm_pepPROBABILITY=0.85', '', 'sequest_rank=2', NULL),
(3, 5, '1', 0, 'Completed', 'Prohits Administrator', '2015-11-25 12:02:19', '', '', ';peptide_min_score:27;requireBoldRed:1;sigthreshold:0.05;report:AUTO;_mudpit:1;sequest_rank:2', 'proex=0,pepex=100,proex_dot=0,pepex_dot=0', 4, 'iProphet:45;46', 'frm_TPP_PARSE_MIN_PROBABILITY=0.05;frm_geneLevelFDR=0.01;frm_pepPROBABILITY=0.85', '', 'sequest_rank=2', NULL),
(4, 3, '1', 0, 'Completed', 'Prohits Administrator', '2015-11-25 14:13:18', '', '', ';peptide_min_score:27;requireBoldRed:1;sigthreshold:0.05;report:AUTO;_mudpit:1;sequest_rank:2', 'proex=0,pepex=100,proex_dot=0,pepex_dot=0', NULL, NULL, 'frm_TPP_PARSE_MIN_PROBABILITY=0.05;frm_geneLevelFDR=0.01;frm_pepPROBABILITY=0.85', 'MSPLIT:45;46', 'sequest_rank=2', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `LTQ_DEMOSearchResults`
--

CREATE TABLE IF NOT EXISTS `LTQ_DEMOSearchResults` (
  `WellID` int(11) NOT NULL DEFAULT '0',
  `TaskID` int(11) NOT NULL DEFAULT '0',
  `DataFiles` text NOT NULL,
  `SearchEngines` varchar(20) NOT NULL DEFAULT '',
  `Date` date DEFAULT NULL,
  `SavedBy` int(4) DEFAULT NULL,
  PRIMARY KEY (`WellID`,`TaskID`,`SearchEngines`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `LTQ_DEMOSearchResults`
--

INSERT INTO `LTQ_DEMOSearchResults` (`WellID`, `TaskID`, `DataFiles`, `SearchEngines`, `Date`, `SavedBy`) VALUES
(7, 1, '../data/20151123/F035980.dat', 'Mascot', '2015-11-23', 1),
(7, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/7_200_NET1_HA_comet.pep.xml', 'COMET', '2015-11-23', NULL),
(4, 1, '../data/20151123/F035976.dat', 'Mascot', '2015-11-23', 1),
(4, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/4_201_AVO1_HA_comet.pep.xml', 'COMET', '2015-11-23', NULL),
(6, 1, '../data/20151123/F035979.dat', 'Mascot', '2015-11-23', 1),
(6, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/6_202_TPK2_comet.pep.xml', 'COMET', '2015-11-23', NULL),
(3, 1, '../data/20151123/F035974.dat', 'Mascot', '2015-11-23', 1),
(3, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/3_30_TPK2_comet.pep.xml', 'COMET', '2015-11-23', NULL),
(5, 1, '../data/20151123/F035977.dat', 'Mascot', '2015-11-23', 1),
(5, 1, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/5_32_ALK2_comet.pep.xml', 'COMET', '2015-11-23', NULL),
(8, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/8_8_MEPCE_comet.pep.xml', 'COMET', '2015-11-23', NULL),
(11, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/11_16_FLAG_Alone_comet.pep.xml', 'COMET', '2015-11-23', NULL),
(9, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/9_14_RAF1_comet.pep.xml', 'COMET', '2015-11-23', NULL),
(8, 2, '../data/20151124/F035986.dat', 'Mascot', '2015-11-24', 1),
(10, 2, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/10_12_WASL_comet.pep.xml', 'COMET', '2015-11-23', NULL),
(9, 2, '../data/20151124/F035987.dat', 'Mascot', '2015-11-24', 1),
(11, 2, '../data/20151124/F035989.dat', 'Mascot', '2015-11-24', 1),
(10, 2, '../data/20151124/F035988.dat', 'Mascot', '2015-11-24', 1),
(46, 3, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task3/Results/46_Swath_MEPCEJune7-Biorep3MSPLITfiltered.txt', 'MSPLIT', '2015-11-24', 1),
(45, 3, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task3/Results/45_Swath_EIF4aJune7-Biorep2MSPLITfiltered.txt', 'MSPLIT', '2015-11-24', 1),
(46, 5, '/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_gpm/46_Swath_MEPCEJune7-Biorep3_Q1.gpm.xml;/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_gpm/46_Swath_MEPCEJune7-Biorep3_Q2.gpm.xml;/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_gpm/46_Swath_MEPCEJune7-Biorep3_Q3.gpm.xml', 'GPM', '2015-11-26', NULL),
(46, 5, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_comet/46_Swath_MEPCEJune7-Biorep3_Q1.pep.xml;/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_comet/46_Swath_MEPCEJune7-Biorep3_Q2.pep.xml;/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_comet/46_Swath_MEPCEJune7-Biorep3_Q3.pep.xml', 'COMET', '2015-11-26', NULL),
(45, 5, '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_comet/45_Swath_EIF4aJune7-Biorep2_Q1.pep.xml;/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_comet/45_Swath_EIF4aJune7-Biorep2_Q2.pep.xml;/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_comet/45_Swath_EIF4aJune7-Biorep2_Q3.pep.xml', 'COMET', '2015-11-26', NULL),
(45, 5, '/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_gpm/45_Swath_EIF4aJune7-Biorep2_Q1.gpm.xml;/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_gpm/45_Swath_EIF4aJune7-Biorep2_Q2.gpm.xml;/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_gpm/45_Swath_EIF4aJune7-Biorep2_Q3.gpm.xml', 'GPM', '2015-11-26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `LTQ_DEMOSearchTasks`
--

CREATE TABLE IF NOT EXISTS `LTQ_DEMOSearchTasks` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `PlateID` varchar(200) DEFAULT NULL,
  `DataFileFormat` varchar(10) DEFAULT NULL,
  `SearchEngines` varchar(250) NOT NULL DEFAULT '',
  `Parameters` text,
  `TaskName` varchar(100) DEFAULT NULL,
  `LCQfilter` varchar(200) DEFAULT NULL,
  `Schedule` varchar(50) DEFAULT NULL,
  `StartTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `RunTPP` int(10) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `ProcessID` varchar(50) DEFAULT NULL,
  `UserID` int(5) DEFAULT NULL,
  `ProjectID` int(11) DEFAULT NULL,
  `AutoAddFile` enum('Yes','No') NOT NULL DEFAULT 'No',
  `DIAUmpire_parameters` text,
  PRIMARY KEY (`ID`),
  KEY `PlateID` (`PlateID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `LTQ_DEMOSearchTasks`
--

INSERT INTO `LTQ_DEMOSearchTasks` (`ID`, `PlateID`, `DataFileFormat`, `SearchEngines`, `Parameters`, `TaskName`, `LCQfilter`, `Schedule`, `StartTime`, `RunTPP`, `Status`, `ProcessID`, `UserID`, `ProjectID`, `AutoAddFile`, `DIAUmpire_parameters`) VALUES
(1, '1', NULL, 'Mascot;COMET=LTQ_FILE;Converter=default;Database=YEAST_Ref57cRapREVg', 'Mascot===INTERMEDIATE=;FORMVER=1.01;SEARCH=MIS;PEAK=AUTO;REPTYPE=peptide;ErrTolRepeat=0;SHOWALLMODS=;USERNAME=prohits;USEREMAIL=prohits@test;COM=LTQ RAW files;CLE=Trypsin;PFA=2;QUANTITATION=None;TAXONOMY=All entries;TOL=3;TOLU=Da;PEP_ISOTOPE_ERROR=0;ITOL=0.6;ITOLU=Da;CHARGE=2+, 3+ and 4+;MASS=Monoisotopic;INSTRUMENT=LCQ-DECA;REPORT=AUTO;SHOWALLMODS=;MODS=;IT_MODS=Oxidation (M);IT_MODS=Deamidated (NQ);DB=YEAST_Ref57cRapREVg;\nCOMET===database_name=YEAST_Ref57cRapREVg;;search_enzyme_number=1;;multiple_select_str=frm_variable_MODS|Oxidation (M):::Deamidated (NQ)&&frm_fixed_MODS|;;allowed_missed_cleavage=2;;num_enzyme_termini=;;decoy_search=;;mass_type_parent=1;;mass_type_fragment=1;;peptide_mass_tolerance=3.00;;peptide_mass_units=0;;fragment_bin_tol=1.0005;;fragment_bin_offset=0.4;;theoretical_fragment_ions=1;;use_NL_ions=1;;isotope_error=0;;CHARGE=2+, 3+ and 4+;;\nfrm_fixed_mod_str===\nfrm_variable_mod_str===Oxidation (M);;Deamidated (NQ)', 'YST_GEL_FREE', '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', NULL, '2015-11-23 17:31:08', 1, 'Finished', NULL, 1, 2, 'No', NULL),
(2, '2', NULL, 'Mascot;COMET=LTQ_FILE;Converter=default;Database=HUMAN_Ref57cRapREVg', 'Mascot===INTERMEDIATE=;FORMVER=1.01;SEARCH=MIS;PEAK=AUTO;REPTYPE=peptide;ErrTolRepeat=0;SHOWALLMODS=;USERNAME=prohits;USEREMAIL=prohits@test;COM=LTQ RAW files;CLE=Trypsin;PFA=2;QUANTITATION=None;TAXONOMY=All entries;TOL=3;TOLU=Da;PEP_ISOTOPE_ERROR=0;ITOL=0.6;ITOLU=Da;CHARGE=2+, 3+ and 4+;MASS=Monoisotopic;INSTRUMENT=LCQ-DECA;REPORT=AUTO;SHOWALLMODS=;MODS=;IT_MODS=Oxidation (M);IT_MODS=Deamidated (NQ);DB=HUMAN_Ref57cRapREVg;\r\nCOMET===database_name=HUMAN_Ref57cRapREVg;;search_enzyme_number=1;;multiple_select_str=frm_variable_MODS|Oxidation (M):::Deamidated (NQ)&&frm_fixed_MODS|;;allowed_missed_cleavage=2;;num_enzyme_termini=;;decoy_search=;;mass_type_parent=1;;mass_type_fragment=1;;peptide_mass_tolerance=3.00;;peptide_mass_units=0;;fragment_bin_tol=1.0005;;fragment_bin_offset=0.4;;theoretical_fragment_ions=1;;use_NL_ions=1;;isotope_error=0;;CHARGE=2+, 3+ and 4+;;\r\nfrm_fixed_mod_str===\r\nfrm_variable_mod_str===Oxidation (M);;Deamidated (NQ)', 'Humna DEOM', '--32 --mz32 --inten32 --filter "peakPicking true 2" --filter "msLevel 2" -g| -proteinpilot /singleprecision', NULL, '2015-11-24 09:59:54', 2, 'Finished', '5950', 1, 3, 'No', NULL),
(3, '2', NULL, 'MSPLIT_LIB=Human_SWATH_Atlas_V1.mgf;Converter=SWATH_PWD;MSPLIT=MSPLIT_default;Database=HUMAN_RefV57cRAPg', '\nMSPLIT===para_FDR:0.01;para_decoy_fragment_mass_tolerane:0.05;para_parent_mass_tolerance:25;para_fragment_mass_tolerance:50;para_number_scans:0;para_maxRT:5;para_minRT:5;dia_win_ms1_start:0;dia_win_ms1_end:1250;dia_SWATH_window_setting:;\nfrm_fixed_mod_str===Carbamidomethyl (C)\nfrm_variable_mod_str===Oxidation (M);;Acetyl (N-term)', 'EIF4A_MEPCE MSPLIT', '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', NULL, '2015-11-24 15:10:11', NULL, 'Finished', 'MSPLIT_10155', 1, 3, 'No', ''),
(5, '2', NULL, 'GPM;COMET=SWATH_TOF;Converter=SWATH_PWD;DIAUmpire=25DaWindowFixed;Database=HUMAN_Ref57cRapREVg', 'GPM===spectrum__fragment_monoisotopic_mass_error=40;spectrum__fragment_monoisotopic_mass_error_units=ppm;spectrum__parent_monoisotopic_mass_error_plus=50;spectrum__parent_monoisotopic_mass_error_minus=50;spectrum__parent_monoisotopic_mass_error_units=ppm;spectrum__parent_monoisotopic_mass_isotope_error=yes;spectrum__fragment_mass_type=monoisotopic;spectrum__use_contrast_angle=no;spectrum__contrast_angle=40;spectrum__maximum_parent_charge=4;refine__spectrum_synthesis=yes;spectrum__use_noise_suppression=no;spectrum__minimum_parent_m99h=600.0;spectrum__minimum_fragment_mz=50;spectrum__total_peaks=160;spectrum__minimum_peaks=3;scoring__b_ions=yes;scoring__y_ions=yes;protein__cleavage_site_select=[RK]|{P};protein__cleavage_site=;protein__cleavage_semi=no;scoring__maximum_missed_cleavage_sites=1;protein__cleavage_C88terminal_mass_change=+17.002735;protein__cleavage_N88terminal_mass_change=+1.007825;output__xsl_path=/tandem/tandem-style.xsl;list_path__default_parameters=../tandem/methods/qstar.xml;output__sort_results_by=protein;output__results=valid;protein__taxon=HUMAN_Ref57cRapREVg;residue__potential_modification_mass=;residue__potential_modification_mass_select=15.99491@M:0.984013@N:0.984014@Q;residue__modification_mass_select=;residue__modification_mass=;refine=no;refine__potential_N88terminus_modifications=+42.010565@[;refine__maximum_valid_expectation_value=10;\nCOMET===database_name=HUMAN_Ref57cRapREVg;;search_enzyme_number=1;;multiple_select_str=frm_variable_MODS|Oxidation (M):::Deamidated (NQ)&&frm_fixed_MODS|;;allowed_missed_cleavage=2;;num_enzyme_termini=;;decoy_search=;;mass_type_parent=1;;mass_type_fragment=1;;peptide_mass_tolerance=35;;peptide_mass_units=2;;fragment_bin_tol=1.0005;;fragment_bin_offset=0.4;;theoretical_fragment_ions=1;;use_NL_ions=1;;isotope_error=0;;CHARGE=2+, 3+ and 4+;;\nfrm_fixed_mod_str===\nfrm_variable_mod_str===Oxidation (M);;Deamidated (NQ)', 'EIF4A and MEPCE Umpire', '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', NULL, '2015-11-26 14:00:40', 4, 'Finished', '21338', 1, 3, 'No', 'dia_PrecursorRank:25;dia_FragmentRank:300;dia_CorrThreshold:0.2;dia_DeltaApex:0.6;para_MS1PPM:30;para_MS2PPM:40;para_SN:2;para_MS2SN:2;para_MinMSIntensity:5;para_MinMSMSIntensity:1;para_MaxCurveRTRange:1;para_Resolution:17000;para_StartCharge:2;para_EndCharge:4;para_MS2StartCharge:2;para_MS2EndCharge:4;para_NoMissedScan:1;para_MinFrag:10;para_EstimateBG:true;dia_WindowType:SWATH;dia_WindowSize:25;dia_SWATH_window_setting:;');

-- --------------------------------------------------------

--
-- Table structure for table `LTQ_DEMOtppResults`
--

CREATE TABLE IF NOT EXISTS `LTQ_DEMOtppResults` (
  `WellID` varchar(250) NOT NULL DEFAULT '0',
  `TppTaskID` int(11) NOT NULL DEFAULT '0',
  `SearchEngine` varchar(50) NOT NULL DEFAULT '',
  `pepXML` varchar(200) NOT NULL DEFAULT '',
  `protXML` varchar(200) NOT NULL DEFAULT '',
  `ProhitsID` varchar(20) DEFAULT NULL,
  `ProjectID` int(11) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `SavedBy` int(4) DEFAULT NULL,
  `User` int(4) DEFAULT NULL,
  PRIMARY KEY (`WellID`,`TppTaskID`,`SearchEngine`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `LTQ_DEMOtppResults`
--

INSERT INTO `LTQ_DEMOtppResults` (`WellID`, `TppTaskID`, `SearchEngine`, `pepXML`, `protXML`, `ProhitsID`, `ProjectID`, `Date`, `SavedBy`, `User`) VALUES
('7', 1, 'Mascot', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/7_200_NET1_HA_mascot.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/7_200_NET1_HA_mascot.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('7', 1, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/7_200_NET1_HA_comet.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/7_200_NET1_HA_comet.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('7', 1, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/7_200_NET1_HA_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/7_200_NET1_HA_combined.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('4', 1, 'Mascot', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/4_201_AVO1_HA_mascot.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/4_201_AVO1_HA_mascot.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('4', 1, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/4_201_AVO1_HA_comet.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/4_201_AVO1_HA_comet.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('4', 1, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/4_201_AVO1_HA_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/4_201_AVO1_HA_combined.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('6', 1, 'Mascot', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/6_202_TPK2_mascot.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/6_202_TPK2_mascot.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('6', 1, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/6_202_TPK2_comet.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/6_202_TPK2_comet.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('6', 1, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/6_202_TPK2_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/6_202_TPK2_combined.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('3', 1, 'Mascot', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/3_30_TPK2_mascot.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/3_30_TPK2_mascot.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('3', 1, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/3_30_TPK2_comet.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/3_30_TPK2_comet.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('3', 1, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/3_30_TPK2_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/3_30_TPK2_combined.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('5', 1, 'Mascot', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/5_32_ALK2_mascot.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/5_32_ALK2_mascot.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('5', 1, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/5_32_ALK2_comet.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/5_32_ALK2_comet.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('5', 1, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/5_32_ALK2_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task1/tpp1/5_32_ALK2_combined.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('11', 2, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/11_16_FLAG_Alone_comet.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/11_16_FLAG_Alone_comet.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('9', 2, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/9_14_RAF1_comet.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/9_14_RAF1_comet.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('10', 2, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/10_12_WASL_comet.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/10_12_WASL_comet.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('8', 2, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/8_8_MEPCE_comet.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/8_8_MEPCE_comet.pep.inter.prot.xml', NULL, NULL, '2015-11-23', 1, NULL),
('9', 2, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/9_14_RAF1_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/9_14_RAF1_combined.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-24', 1, NULL),
('10', 2, 'Mascot', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/10_12_WASL_mascot.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/10_12_WASL_mascot.pep.inter.prot.xml', NULL, NULL, '2015-11-24', 1, NULL),
('10', 2, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/10_12_WASL_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/10_12_WASL_combined.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-24', 1, NULL),
('9', 2, 'Mascot', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/9_14_RAF1_mascot.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/9_14_RAF1_mascot.pep.inter.prot.xml', NULL, NULL, '2015-11-24', 1, NULL),
('11', 2, 'Mascot', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/11_16_FLAG_Alone_mascot.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/11_16_FLAG_Alone_mascot.pep.inter.prot.xml', NULL, NULL, '2015-11-24', 1, NULL),
('11', 2, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/11_16_FLAG_Alone_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/11_16_FLAG_Alone_combined.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-24', 1, NULL),
('8', 2, 'Mascot', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/8_8_MEPCE_mascot.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/8_8_MEPCE_mascot.pep.inter.prot.xml', NULL, NULL, '2015-11-24', 1, NULL),
('8', 2, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/8_8_MEPCE_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task2/tpp2/8_8_MEPCE_combined.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-24', 1, NULL),
('45', 3, 'GPM', '', '', NULL, NULL, '2015-11-24', NULL, NULL),
('45', 4, 'GPM', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_gpm/GPM_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_gpm/GPM_combined.pep.inter.prot.xml', NULL, NULL, '2015-11-26', NULL, NULL),
('45', 4, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_comet/COMET_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/45_Swath_EIF4aJune7-Biorep2_comet/COMET_combined.pep.inter.prot.xml', NULL, NULL, '2015-11-26', NULL, NULL),
('45', 4, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/tpp4/interact-45_Swath_EIF4aJune7-Biorep2_Q3.pep.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/tpp4/45_Swath_EIF4aJune7-Biorep2.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-26', 1, NULL),
('46', 4, 'GPM', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_gpm/GPM_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_gpm/GPM_combined.pep.inter.prot.xml', NULL, NULL, '2015-11-26', NULL, NULL),
('46', 4, 'COMET', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_comet/COMET_combined.pep.inter.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/46_Swath_MEPCEJune7-Biorep3_comet/COMET_combined.pep.inter.prot.xml', NULL, NULL, '2015-11-26', NULL, NULL),
('46', 4, 'iProphet', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/tpp4/interact-46_Swath_MEPCEJune7-Biorep3_Q3.pep.xml', '/var/www/html/Prohits/EXT/thegpm/gpm/archive/LTQ_DEMO/task5/tpp4/46_Swath_MEPCEJune7-Biorep3.pep.inter.iproph.prot.xml', NULL, NULL, '2015-11-26', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `LTQ_DEMOtppTasks`
--

CREATE TABLE IF NOT EXISTS `LTQ_DEMOtppTasks` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `SearchTaskID` int(11) DEFAULT NULL,
  `ParamSetName` varchar(50) NOT NULL DEFAULT '',
  `Parameters` text,
  `TaskName` varchar(100) DEFAULT NULL,
  `StartTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Status` varchar(50) DEFAULT NULL,
  `ProcessID` varchar(50) DEFAULT NULL,
  `UserID` int(5) DEFAULT NULL,
  `ProjectID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `SearchTaskID` (`SearchTaskID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `LTQ_DEMOtppTasks`
--

INSERT INTO `LTQ_DEMOtppTasks` (`ID`, `SearchTaskID`, `ParamSetName`, `Parameters`, `TaskName`, `StartTime`, `Status`, `ProcessID`, `UserID`, `ProjectID`) VALUES
(1, 1, 'default', 'frm_general:-p0.05 -x20 -PPM -dDECOY\nfrm_iProphet:pPRIME\nfrm_peptideProphet:pdP\nfrm_xpress:\nfrm_asap:\nfrm_libra:\nfrm_refreshParser:\n', 'YST', '2015-11-23 17:30:25', 'Finished', '28443', 1, 2),
(2, 2, 'default', 'frm_general:-p0.05 -x20 -PPM -dDECOY\nfrm_iProphet:pPRIME\nfrm_peptideProphet:pdP\nfrm_xpress:\nfrm_asap:\nfrm_libra:\nfrm_refreshParser:\n', 'HM', '2015-11-24 09:59:54', 'Finished', '6532', 1, 3),
(4, 5, 'SWATH_Umpire', 'frm_general:-p0.05 -x20 -PPM -dDECOY\nfrm_iProphet:pPRIME\nfrm_peptideProphet:pPAEd\nfrm_xpress:\nfrm_asap:\nfrm_libra:\nfrm_refreshParser:\n', 'swath test', '2015-11-26 14:00:40', 'Finished', '32164', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `MergedFiles`
--

CREATE TABLE IF NOT EXISTS `MergedFiles` (
  `TableName` varchar(25) NOT NULL DEFAULT '',
  `MergedID` int(11) NOT NULL DEFAULT '0',
  `MergedType` varchar(12) NOT NULL DEFAULT '',
  `ID_str` varchar(200) NOT NULL DEFAULT '',
  `MergedName` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`TableName`,`MergedID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `SearchParameter`
--

CREATE TABLE IF NOT EXISTS `SearchParameter` (
  `ID` mediumint(5) NOT NULL AUTO_INCREMENT,
  `Name` varchar(150) NOT NULL DEFAULT '',
  `Type` varchar(254) NOT NULL DEFAULT '',
  `User` varchar(50) NOT NULL DEFAULT '',
  `Date` date NOT NULL DEFAULT '0000-00-00',
  `ProjectID` int(5) NOT NULL DEFAULT '0',
  `Parameters` text NOT NULL,
  `SWATH` tinyint(1) DEFAULT NULL,
  `Default` tinyint(1) DEFAULT NULL,
  `Machine` varchar(220) DEFAULT NULL,
  `Description` text,
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `SearchParameter`
--

INSERT INTO `SearchParameter` (`ID`, `Name`, `Type`, `User`, `Date`, `ProjectID`, `Parameters`, `SWATH`, `Default`, `Machine`, `Description`) VALUES
(1, 'default', 'Converter', '1', '2017-09-26', 0, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 0, 0, 'LTQ_DEMO', 'only for LTQ_DEMO machine.'),
(2, 'SWATH_PWD', 'Converter', '1', '2017-09-26', 0, '--32 --mz32 --inten32 --filter "peakPicking true 1-" -g| -centroid /singleprecision', 1, 0, 'LTQ_DEMO', 'only for LTQ_DEMO machine.'),
(3, 'default modifications', 'Modifications', '0', '2015-11-23', 0, 'Fixed=\nVariable=Oxidation (M)\nOther=Deamidated (NQ);;Carbamidomethyl (C);;Phospho (ST);;Phospho (Y);;Acetyl (N-term)', NULL, NULL, NULL, NULL),
(4, 'LTQ_FILE', 'LTQ_DEMO', '1', '2017-09-26', 0, '\nMASCOT===INTERMEDIATE=;;FORMVER=1.01;;SEARCH=MIS;;PEAK=AUTO;;REPTYPE=peptide;;ErrTolRepeat=0;;SHOWALLMODS=;;USERNAME=prohits;;USEREMAIL=prohits@test;;COM=LTQ RAW files;;CLE=Trypsin;;PFA=2;;QUANTITATION=None;;TAXONOMY=All entries;;TOL=3;;TOLU=Da;;PEP_ISOTOPE_ERROR=0;;ITOL=0.6;;ITOLU=Da;;CHARGE=2+, 3+ and 4+;;MASS=Monoisotopic;;INSTRUMENT=LCQ-DECA;;REPORT=AUTO;;SHOWALLMODS=;;MODS=;;IT_MODS=;;DB=;;\nGPM===frm_form_obj_type_str=;protein__taxon=select_MULTIPLE;protein__taxon1=select_MULTIPLE;scoring__include_reverse=checkbox;protein__modified_residue_mass_file=checkbox;disabled=button;output__maximum_valid_expectation_value=select;spectrum__fragment_monoisotopic_mass_error=text;spectrum__fragment_monoisotopic_mass_error_units=select;spectrum__parent_monoisotopic_mass_error_plus=text;spectrum__parent_monoisotopic_mass_error_minus=text;spectrum__parent_monoisotopic_mass_error_units=select;spectrum__parent_monoisotopic_mass_isotope_error=radio;spectrum__parent_monoisotopic_mass_isotope_error=radio;spectrum__fragment_mass_type=radio;spectrum__fragment_mass_type=radio;spectrum__use_contrast_angle=radio;spectrum__use_contrast_angle=radio;spectrum__contrast_angle=text;spectrum__maximum_parent_charge=text;refine__spectrum_synthesis=radio;refine__spectrum_synthesis=radio;spectrum__use_noise_suppression=radio;spectrum__use_noise_suppression=radio;spectrum__minimum_parent_m99h=text;spectrum__minimum_fragment_mz=text;spectrum__total_peaks=text;spectrum__minimum_peaks=text;scoring__a_ions=checkbox;scoring__b_ions=checkbox;scoring__c_ions=checkbox;scoring__x_ions=checkbox;scoring__y_ions=checkbox;scoring__z_ions=checkbox;protein__cleavage_site_select=select;protein__cleavage_site=text;protein__cleavage_semi=radio;protein__cleavage_semi=radio;scoring__maximum_missed_cleavage_sites=text;protein__cleavage_C88terminal_mass_change=text;protein__cleavage_N88terminal_mass_change=text;output__xsl_path=hidden;list_path__default_parameters=hidden;output__xsl_path=hidden;output__sort_results_by=hidden;output__results=hidden;;spectrum__fragment_monoisotopic_mass_error=0.6;;spectrum__fragment_monoisotopic_mass_error_units=Daltons;;spectrum__parent_monoisotopic_mass_error_plus=1.6;;spectrum__parent_monoisotopic_mass_error_minus=1.6;;spectrum__parent_monoisotopic_mass_error_units=Daltons;;spectrum__parent_monoisotopic_mass_isotope_error=yes;;spectrum__fragment_mass_type=monoisotopic;;spectrum__use_contrast_angle=no;;spectrum__contrast_angle=40;;spectrum__maximum_parent_charge=4;;refine__spectrum_synthesis=yes;;spectrum__use_noise_suppression=no;;spectrum__minimum_parent_m99h=500.0;;spectrum__minimum_fragment_mz=150.0;;spectrum__total_peaks=50;;spectrum__minimum_peaks=15;;scoring__b_ions=yes;;scoring__y_ions=yes;;protein__cleavage_site_select=[RK]|{P};;protein__cleavage_site=;;protein__cleavage_semi=no;;scoring__maximum_missed_cleavage_sites=2;;protein__cleavage_C88terminal_mass_change=+17.002735;;protein__cleavage_N88terminal_mass_change=+1.007825;;output__xsl_path=/tandem/tandem-style.xsl;;list_path__default_parameters=../tandem/methods/qstar.xml;;output__sort_results_by=protein;;output__results=valid;;protein__taxon=;;residue__potential_modification_mass=;;residue__potential_modification_mass_select=;;residue__modification_mass_select=;;residue__modification_mass=;;refine=no;;refine__potential_N88terminus_modifications=+42.010565@[;;refine__maximum_valid_expectation_value=10;;\nCOMET===database_name=;;search_enzyme_number=1;;multiple_select_str=;;allowed_missed_cleavage=2;;num_enzyme_termini=;;decoy_search=;;mass_type_parent=1;;mass_type_fragment=1;;peptide_mass_tolerance=3.00;;peptide_mass_units=0;;fragment_bin_tol=1.0005;;fragment_bin_offset=0.4;;theoretical_fragment_ions=1;;use_NL_ions=1;;isotope_error=0;;CHARGE=2+, 3+ and 4+;;\nMSGFPL===database_name=;;enzyme_number=1;;multiple_select_str=;;num_enzyme_termini=;;decoy_search=;;peptide_mass_tolerance_start=0.6;;peptide_mass_tolerance_end=1.6;;peptide_mass_units=1;;isotope_error_start=0;;isotope_error_end=1;;CHARGE=2+, 3+ and 4+;;msgfpl_FragmentMethodID=0;;msgfpl_InstrumentID=0;;', 0, 0, 'LTQ_DEMO', ''),
(5, 'SWATH_TOF', 'LTQ_DEMO', '1', '2017-09-26', 0, '\nMASCOT===INTERMEDIATE=;;FORMVER=1.01;;SEARCH=MIS;;PEAK=AUTO;;REPTYPE=peptide;;ErrTolRepeat=0;;SHOWALLMODS=;;USERNAME=prohits;;USEREMAIL=prohits@test;;COM=SWATH TOF files;;CLE=Trypsin;;PFA=2;;QUANTITATION=None;;TAXONOMY=All entries;;TOL=35;;TOLU=ppm;;PEP_ISOTOPE_ERROR=0;;ITOL=0.15;;ITOLU=Da;;CHARGE=2+, 3+ and 4+;;MASS=Monoisotopic;;INSTRUMENT=PULSAR;;REPORT=AUTO;;SHOWALLMODS=;;MODS=;;IT_MODS=;;DB=;;\nGPM===frm_form_obj_type_str=;protein__taxon=select_MULTIPLE;protein__taxon1=select_MULTIPLE;scoring__include_reverse=checkbox;protein__modified_residue_mass_file=checkbox;disabled=button;output__maximum_valid_expectation_value=select;spectrum__fragment_monoisotopic_mass_error=text;spectrum__fragment_monoisotopic_mass_error_units=select;spectrum__parent_monoisotopic_mass_error_plus=text;spectrum__parent_monoisotopic_mass_error_minus=text;spectrum__parent_monoisotopic_mass_error_units=select;spectrum__parent_monoisotopic_mass_isotope_error=radio;spectrum__parent_monoisotopic_mass_isotope_error=radio;spectrum__fragment_mass_type=radio;spectrum__fragment_mass_type=radio;spectrum__use_contrast_angle=radio;spectrum__use_contrast_angle=radio;spectrum__contrast_angle=text;spectrum__maximum_parent_charge=text;refine__spectrum_synthesis=radio;refine__spectrum_synthesis=radio;spectrum__use_noise_suppression=radio;spectrum__use_noise_suppression=radio;spectrum__minimum_parent_m99h=text;spectrum__minimum_fragment_mz=text;spectrum__total_peaks=text;spectrum__minimum_peaks=text;scoring__a_ions=checkbox;scoring__b_ions=checkbox;scoring__c_ions=checkbox;scoring__x_ions=checkbox;scoring__y_ions=checkbox;scoring__z_ions=checkbox;protein__cleavage_site_select=select;protein__cleavage_site=text;protein__cleavage_semi=radio;protein__cleavage_semi=radio;scoring__maximum_missed_cleavage_sites=text;protein__cleavage_C88terminal_mass_change=text;protein__cleavage_N88terminal_mass_change=text;output__xsl_path=hidden;list_path__default_parameters=hidden;output__xsl_path=hidden;output__sort_results_by=hidden;output__results=hidden;;spectrum__fragment_monoisotopic_mass_error=40;;spectrum__fragment_monoisotopic_mass_error_units=ppm;;spectrum__parent_monoisotopic_mass_error_plus=50;;spectrum__parent_monoisotopic_mass_error_minus=50;;spectrum__parent_monoisotopic_mass_error_units=ppm;;spectrum__parent_monoisotopic_mass_isotope_error=yes;;spectrum__fragment_mass_type=monoisotopic;;spectrum__use_contrast_angle=no;;spectrum__contrast_angle=40;;spectrum__maximum_parent_charge=4;;refine__spectrum_synthesis=yes;;spectrum__use_noise_suppression=no;;spectrum__minimum_parent_m99h=600.0;;spectrum__minimum_fragment_mz=50;;spectrum__total_peaks=160;;spectrum__minimum_peaks=3;;scoring__b_ions=yes;;scoring__y_ions=yes;;protein__cleavage_site_select=[RK]|{P};;protein__cleavage_site=;;protein__cleavage_semi=no;;scoring__maximum_missed_cleavage_sites=1;;protein__cleavage_C88terminal_mass_change=+17.002735;;protein__cleavage_N88terminal_mass_change=+1.007825;;output__xsl_path=/tandem/tandem-style.xsl;;list_path__default_parameters=../tandem/methods/qstar.xml;;output__sort_results_by=protein;;output__results=valid;;protein__taxon=;;residue__potential_modification_mass=;;residue__potential_modification_mass_select=;;residue__modification_mass_select=;;residue__modification_mass=;;refine=no;;refine__potential_N88terminus_modifications=+42.010565@[;;refine__maximum_valid_expectation_value=10;;\nCOMET===database_name=;;search_enzyme_number=1;;multiple_select_str=;;allowed_missed_cleavage=2;;num_enzyme_termini=;;decoy_search=;;mass_type_parent=1;;mass_type_fragment=1;;peptide_mass_tolerance=35;;peptide_mass_units=2;;fragment_bin_tol=1.0005;;fragment_bin_offset=0.4;;theoretical_fragment_ions=1;;use_NL_ions=1;;isotope_error=0;;CHARGE=2+, 3+ and 4+;;\nMSGFPL===database_name=;;enzyme_number=1;;multiple_select_str=;;num_enzyme_termini=;;decoy_search=;;peptide_mass_tolerance_start=50;;peptide_mass_tolerance_end=50;;peptide_mass_units=2;;isotope_error_start=0;;isotope_error_end=1;;CHARGE=2+, 3+ and 4+;;msgfpl_FragmentMethodID=0;;msgfpl_InstrumentID=2;;\nMSGFDB===database_name=;;enzyme_number=1;;multiple_select_str=;;num_enzyme_termini=0;;decoy_search=1;;peptide_mass_tolerance_start=50;;peptide_mass_tolerance_end=50;;peptide_mass_units=2;;c13=1;;minPepLength=8;;maxPepLength=30;;minPrecursorCharge=2;;maxPrecursorCharge=4;;msgfdb_FragmentMethodID=0;;msgfdb_InstrumentID=2;;numMatchesPerSpec=1;;uniformAAProb=;;', 0, 0, 'LTQ_DEMO', ''),
(6, 'ORBIELITE_FILE', 'LTQ_DEMO', '1', '2017-09-26', 0, '\nMASCOT===INTERMEDIATE=;;FORMVER=1.01;;SEARCH=MIS;;PEAK=AUTO;;REPTYPE=peptide;;ErrTolRepeat=0;;SHOWALLMODS=;;USERNAME=prohits;;USEREMAIL=prohits@prohits;;COM=ORBI ELITE;;CLE=Trypsin;;PFA=2;;QUANTITATION=None;;TAXONOMY=All entries;;TOL=12;;TOLU=ppm;;PEP_ISOTOPE_ERROR=0;;ITOL=0.6;;ITOLU=Da;;CHARGE=2+, 3+ and 4+;;MASS=Monoisotopic;;INSTRUMENT=LCQ-DECA;;REPORT=AUTO;;SHOWALLMODS=;;MODS=;;IT_MODS=;;DB=;;\nGPM===frm_form_obj_type_str=;protein__taxon=select_MULTIPLE;protein__taxon1=select_MULTIPLE;scoring__include_reverse=checkbox;protein__modified_residue_mass_file=checkbox;disabled=button;output__maximum_valid_expectation_value=select;spectrum__fragment_monoisotopic_mass_error=text;spectrum__fragment_monoisotopic_mass_error_units=select;spectrum__parent_monoisotopic_mass_error_plus=text;spectrum__parent_monoisotopic_mass_error_minus=text;spectrum__parent_monoisotopic_mass_error_units=select;spectrum__parent_monoisotopic_mass_isotope_error=radio;spectrum__parent_monoisotopic_mass_isotope_error=radio;spectrum__fragment_mass_type=radio;spectrum__fragment_mass_type=radio;spectrum__use_contrast_angle=radio;spectrum__use_contrast_angle=radio;spectrum__contrast_angle=text;spectrum__maximum_parent_charge=text;refine__spectrum_synthesis=radio;refine__spectrum_synthesis=radio;spectrum__use_noise_suppression=radio;spectrum__use_noise_suppression=radio;spectrum__minimum_parent_m99h=text;spectrum__minimum_fragment_mz=text;spectrum__total_peaks=text;spectrum__minimum_peaks=text;scoring__a_ions=checkbox;scoring__b_ions=checkbox;scoring__c_ions=checkbox;scoring__x_ions=checkbox;scoring__y_ions=checkbox;scoring__z_ions=checkbox;protein__cleavage_site_select=select;protein__cleavage_site=text;protein__cleavage_semi=radio;protein__cleavage_semi=radio;scoring__maximum_missed_cleavage_sites=text;protein__cleavage_C88terminal_mass_change=text;protein__cleavage_N88terminal_mass_change=text;output__xsl_path=hidden;list_path__default_parameters=hidden;output__xsl_path=hidden;output__sort_results_by=hidden;output__results=hidden;;spectrum__fragment_monoisotopic_mass_error=0.6;;spectrum__fragment_monoisotopic_mass_error_units=Daltons;;spectrum__parent_monoisotopic_mass_error_plus=12;;spectrum__parent_monoisotopic_mass_error_minus=12;;spectrum__parent_monoisotopic_mass_error_units=ppm;;spectrum__parent_monoisotopic_mass_isotope_error=yes;;spectrum__fragment_mass_type=monoisotopic;;spectrum__use_contrast_angle=no;;spectrum__contrast_angle=40;;spectrum__maximum_parent_charge=4;;refine__spectrum_synthesis=yes;;spectrum__use_noise_suppression=no;;spectrum__minimum_parent_m99h=500.0;;spectrum__minimum_fragment_mz=150.0;;spectrum__total_peaks=50;;spectrum__minimum_peaks=15;;scoring__b_ions=yes;;scoring__y_ions=yes;;protein__cleavage_site_select=[RK]|{P};;protein__cleavage_site=;;protein__cleavage_semi=no;;scoring__maximum_missed_cleavage_sites=1;;protein__cleavage_C88terminal_mass_change=+17.002735;;protein__cleavage_N88terminal_mass_change=+1.007825;;output__xsl_path=/tandem/tandem-style.xsl;;list_path__default_parameters=../tandem/methods/qstar.xml;;output__sort_results_by=protein;;output__results=valid;;protein__taxon=;;residue__potential_modification_mass=;;residue__potential_modification_mass_select=;;residue__modification_mass_select=;;residue__modification_mass=;;refine=no;;refine__potential_N88terminus_modifications=+42.010565@[;;refine__maximum_valid_expectation_value=10;;\nCOMET===database_name=;;search_enzyme_number=1;;multiple_select_str=;;allowed_missed_cleavage=2;;num_enzyme_termini=;;decoy_search=;;mass_type_parent=1;;mass_type_fragment=1;;peptide_mass_tolerance=12;;peptide_mass_units=2;;fragment_bin_tol=1.0005;;fragment_bin_offset=0.4;;theoretical_fragment_ions=1;;use_NL_ions=1;;isotope_error=0;;CHARGE=2+, 3+ and 4+;;\nMSGFPL===database_name=;;enzyme_number=1;;multiple_select_str=;;num_enzyme_termini=;;decoy_search=;;peptide_mass_tolerance_start=12;;peptide_mass_tolerance_end=20;;peptide_mass_units=2;;isotope_error_start=0;;isotope_error_end=1;;CHARGE=2+, 3+ and 4+;;msgfpl_FragmentMethodID=0;;msgfpl_InstrumentID=1;;', 0, 0, 'LTQ_DEMO', ''),
(7, 'default', 'TPP', '1', '2017-09-26', 0, 'plsp_peptideprophet:--minprob 0.05 --ppm --decoy DECOY --decoyprobs --nonparam\nplsp_iprophet:--nonsp --nonrs --nonsi --nonsm --nonse\nplsp_proteinprophet:--maxppmdiff 20', 0, 1, '', 'it is default'),
(8, 'SWATH_Umpire', 'TPP', '1', '2017-09-26', 0, 'plsp_peptideprophet:--minprob 0.05 --ppm --decoy DECOY --nonparam --accmass --expectscore --decoyprobs\nplsp_iprophet:--nonsp --nonrs --nonsi --nonsm --nonse\nplsp_proteinprophet:--maxppmdiff 20', 1, 0, '', 'it is swath default parameter set'),
(9, '25DaWindowFixed', 'DIAUmpire', '1', '2015-11-23', 0, 'dia_PrecursorRank:25;dia_FragmentRank:300;dia_CorrThreshold:0.2;dia_DeltaApex:0.6;para_MS1PPM:30;para_MS2PPM:40;para_SN:2;para_MS2SN:2;para_MinMSIntensity:5;para_MinMSMSIntensity:1;para_MaxCurveRTRange:1;para_Resolution:17000;para_StartCharge:2;para_EndCharge:4;para_MS2StartCharge:2;para_MS2EndCharge:4;para_NoMissedScan:1;para_MinFrag:10;para_EstimateBG:true;dia_WindowType:SWATH;dia_WindowSize:25;dia_SWATH_window_setting:;', NULL, NULL, NULL, NULL),
(10, 'default', 'MSPLIT', '1', '2015-11-24', 0, 'para_FDR:0.01;para_decoy_fragment_mass_tolerane:0.03;para_parent_mass_tolerance:25;para_fragment_mass_tolerance:50;para_number_scans:0;para_maxRT:5;para_minRT:5;dia_win_ms1_start:0;dia_win_ms1_end:1250;dia_SWATH_window_setting:;', NULL, NULL, NULL, NULL),
(11, 'MSPLIT_default', 'MSPLIT', '1', '2015-11-24', 0, 'para_FDR:0.01;para_decoy_fragment_mass_tolerane:0.05;para_parent_mass_tolerance:25;para_fragment_mass_tolerance:50;para_number_scans:0;para_maxRT:5;para_minRT:5;dia_win_ms1_start:0;dia_win_ms1_end:1250;dia_SWATH_window_setting:;', NULL, NULL, NULL, NULL);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
