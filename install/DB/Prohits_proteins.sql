-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: xtandem2.mshri.on.ca
-- Generation Time: Aug 10, 2017 at 02:53 PM
-- Server version: 5.1.73
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ProhitsNew_proteins`
--

-- --------------------------------------------------------

--
-- Table structure for table `iRefIndex`
--

CREATE TABLE IF NOT EXISTS `iRefIndex` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `geneIDA` int(10) DEFAULT NULL,
  `geneIDB` int(10) DEFAULT NULL,
  `method` varchar(256) DEFAULT NULL,
  `pmids` text,
  `taxa` int(10) DEFAULT NULL,
  `taxb` int(10) DEFAULT NULL,
  `sourcedb` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `geneIDA` (`geneIDA`),
  KEY `geneIDB` (`geneIDB`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=814542 ;

-- --------------------------------------------------------

--
-- Table structure for table `NCBI_gene2go`
--

CREATE TABLE IF NOT EXISTS `NCBI_gene2go` (
  `tax_id` varchar(20) NOT NULL DEFAULT '',
  `GeneID` varchar(20) NOT NULL DEFAULT '',
  `GO_ID` varchar(20) NOT NULL DEFAULT '',
  `Evidence` varchar(20) NOT NULL DEFAULT '',
  `Qualifier` varchar(20) NOT NULL DEFAULT '',
  `GO_term` varchar(254) NOT NULL DEFAULT '',
  `PubMed` varchar(20) NOT NULL DEFAULT '',
  `Category` varchar(40) DEFAULT NULL,
  KEY `GeneID` (`GeneID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `NCBI_gene2refseq`
--

CREATE TABLE IF NOT EXISTS `NCBI_gene2refseq` (
  `tax_id` varchar(20) NOT NULL DEFAULT '',
  `GeneID` varchar(20) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT '',
  `RNA_nucleotide_acc` varchar(20) NOT NULL DEFAULT '',
  `RNA_nucleotide_gi` varchar(20) NOT NULL DEFAULT '',
  `protein_acc` varchar(20) NOT NULL DEFAULT '',
  `protein_gi` varchar(20) NOT NULL DEFAULT '',
  `genomic_nucleotide_acc` varchar(20) NOT NULL DEFAULT '',
  `genomic_nucleotide_gi` varchar(20) NOT NULL DEFAULT '',
  `start_position` varchar(20) NOT NULL DEFAULT '',
  `end_positon` varchar(20) NOT NULL DEFAULT '',
  `orientation` varchar(20) NOT NULL DEFAULT '',
  `assembly` varchar(20) DEFAULT NULL,
  `mature_peptide_accession` varchar(30) DEFAULT NULL,
  `mmature_peptide_gi` varchar(25) DEFAULT NULL,
  `Symbol` varchar(15) DEFAULT NULL,
  KEY `protein_gi` (`protein_gi`),
  KEY `GeneID` (`GeneID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `NCBI_gene2unigene`
--

CREATE TABLE IF NOT EXISTS `NCBI_gene2unigene` (
  `GeneID` varchar(20) NOT NULL DEFAULT '',
  `UniGene_cluster` varchar(100) DEFAULT NULL,
  KEY `GeneID` (`GeneID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `NCBI_gi_tax`
--

CREATE TABLE IF NOT EXISTS `NCBI_gi_tax` (
  `GI` varchar(20) NOT NULL DEFAULT '',
  `TaxID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`GI`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `NCBI_tax_names`
--

CREATE TABLE IF NOT EXISTS `NCBI_tax_names` (
  `TaxID` int(15) NOT NULL DEFAULT '0',
  `name_txt` varchar(100) NOT NULL DEFAULT '',
  `unique_name` varchar(100) NOT NULL DEFAULT '',
  `name_class` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`TaxID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `NCBI_tax_nodes`
--

CREATE TABLE IF NOT EXISTS `NCBI_tax_nodes` (
  `Tax_id` int(11) NOT NULL DEFAULT '0',
  `Parent_tax_id` int(11) NOT NULL DEFAULT '0',
  `Rank` varchar(64) NOT NULL DEFAULT '',
  `Embl_code` varchar(16) NOT NULL DEFAULT '',
  `Division_id` int(11) NOT NULL DEFAULT '0',
  `Inherited_div_flag` tinyint(1) NOT NULL DEFAULT '0',
  `Genetic_code_id` int(11) NOT NULL DEFAULT '0',
  `Inherited_GC_flag` tinyint(1) NOT NULL DEFAULT '0',
  `Mitochondrial_genetic_code_id` int(11) NOT NULL DEFAULT '0',
  `Inherited_MGC_flag` tinyint(1) NOT NULL DEFAULT '0',
  `GenBank_hidden_flag` tinyint(1) NOT NULL DEFAULT '0',
  `Hidden_subtree_root_flag` tinyint(1) NOT NULL DEFAULT '0',
  `Comments` text NOT NULL,
  KEY `Tax_id` (`Tax_id`),
  KEY `Parent_tax_id` (`Parent_tax_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Protein_Accession`
--

CREATE TABLE IF NOT EXISTS `Protein_Accession` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `EntrezGeneID` int(10) DEFAULT NULL,
  `GI` bigint(20) DEFAULT NULL,
  `Acc` varchar(20) DEFAULT NULL,
  `Acc_Version` varchar(20) DEFAULT NULL,
  `UniProtID` varchar(20) DEFAULT NULL,
  `Description` varchar(254) DEFAULT NULL,
  `Source` varchar(10) DEFAULT NULL,
  `SequenceID` int(11) DEFAULT NULL,
  `Status` varchar(40) DEFAULT NULL,
  `TaxID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `EntrezGeneID` (`EntrezGeneID`),
  KEY `Acc` (`Acc`),
  KEY `Acc_Version` (`Acc_Version`),
  KEY `UniProtID` (`UniProtID`),
  KEY `SequenceID` (`SequenceID`),
  KEY `GI` (`GI`),
  KEY `Acc_Version_2` (`Acc_Version`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3173418 ;

-- --------------------------------------------------------

--
-- Table structure for table `Protein_AccessionENS`
--

CREATE TABLE IF NOT EXISTS `Protein_AccessionENS` (
  `ENSP` varchar(50) NOT NULL DEFAULT '',
  `ENSG` varchar(50) DEFAULT NULL,
  `EntrezGeneID` int(11) DEFAULT NULL,
  `Description` varchar(250) DEFAULT NULL,
  `GeneName` varchar(100) DEFAULT NULL,
  `Acc` varchar(50) DEFAULT NULL,
  `SequenceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`ENSP`),
  KEY `SequenceID` (`SequenceID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Protein_AccessionIPI`
--

CREATE TABLE IF NOT EXISTS `Protein_AccessionIPI` (
  `IPI` varchar(40) NOT NULL DEFAULT '',
  `IPI_Version` varchar(15) DEFAULT NULL,
  `EntrezGeneID` int(11) DEFAULT NULL,
  `GeneName` varchar(20) NOT NULL DEFAULT '',
  `Description` varchar(250) NOT NULL DEFAULT '',
  `Acc` varchar(40) NOT NULL DEFAULT '',
  `TaxID` int(7) NOT NULL DEFAULT '0',
  `SequenceID` int(11) DEFAULT NULL,
  PRIMARY KEY (`IPI`),
  KEY `IPI_Version` (`IPI_Version`),
  KEY `SequenceID` (`SequenceID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Protein_Class`
--

CREATE TABLE IF NOT EXISTS `Protein_Class` (
  `EntrezGeneID` int(11) NOT NULL DEFAULT '0',
  `LocusTag` varchar(20) DEFAULT NULL,
  `GeneName` varchar(20) DEFAULT NULL,
  `GeneAliase` varchar(100) DEFAULT NULL,
  `TaxID` int(7) DEFAULT NULL,
  `Description` text,
  `Status` varchar(20) DEFAULT NULL,
  `BioFilter` set('RP','HS','CP','HP','KT','AT','PS','TE','DB','NP','HT','AL') DEFAULT NULL,
  PRIMARY KEY (`EntrezGeneID`),
  KEY `GeneName` (`GeneName`),
  KEY `LocusTag` (`LocusTag`),
  KEY `TaxID` (`TaxID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Protein_ClassENS`
--

CREATE TABLE IF NOT EXISTS `Protein_ClassENS` (
  `ENSG` varchar(100) NOT NULL DEFAULT '',
  `GeneName` varchar(50) DEFAULT NULL,
  `TaxID` int(7) DEFAULT NULL,
  PRIMARY KEY (`ENSG`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Protein_mapping`
--

CREATE TABLE IF NOT EXISTS `Protein_mapping` (
  `UniProtKB` varchar(100) NOT NULL,
  `UniProtID` varchar(100) NOT NULL,
  `GeneID` int(10) DEFAULT NULL,
  `Acc_Version` varchar(100) DEFAULT NULL,
  `TaxID` int(11) DEFAULT NULL,
  KEY `RefSeq` (`Acc_Version`),
  KEY `UniProtKB` (`UniProtKB`),
  KEY `Acc_Version` (`Acc_Version`),
  KEY `GeneID` (`GeneID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Protein_Sequence`
--

CREATE TABLE IF NOT EXISTS `Protein_Sequence` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Sequence` text NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=143182 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
