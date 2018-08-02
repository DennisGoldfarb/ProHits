<?php
/***********************************************************************
 Copyright 2010 Gingras and Tyers labs, 
 Samuel Lunenfeld Research Institute, Mount Sinai Hospital.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*************************************************************************/

/*
$ant_hostCellType = 'abc cell type';
$intActorIndex = 0;
$source_array = array(
  'ProhitsVersion'=>'V1.0.1',
  'ProhitsReleaseDate'=>'2010-01-16',
  'ProhitsLable'=>'Prohtis',
  
  'ProhitsInstitute'=>'mshri',
  'ProhitsAddress'=>'my institute address',
  'ProhitsAdminEmail'=>'gliu@mshri.on.ca'
);
$exp_array = array(
  'ID'=>1,
  'pubTitle'=>'unpublished',
  'pubJournal'=>'Mol. Cell',
  'pubmedID'=>'18782753',
  'pubFirstAuthor'=>'Guomin Liu',
  'pubAuthorList'=>'F Liu, D Long',
  'pubYear'=>'2001',
  'pubContactEmail'=>'gliu@mshri.on.ca',
  
  'hostTaxId'=>'9606',
  'hostOrganismLable'=>'human',
  'hostOrganismFullName'=>"Homo sapiens $ant_hostCellType",
  'interMethodLabel'=>'anti tag coip',
  'interMethodFullName'=>'anti tag coimmunoprecipitation',
  'interMethod_miID'=>'MI:0007',
  'prohitsProjectID'=>'54',
);

$intActorIndex++;
$intActor_array = array(
  'interactorIndex'=>$intActorIndex,
  'baitID'=>'2345',   // changed
  'protGeneName'=>'YCK1',
  'dbVersion'=>'V_33',
  'prohitsID'=>'7655',  ///removed
  'protDB'=>'refseq',
  'proDB_miID'=>'MI:0481',
  'protID'=>'NP_056202.2',
  
  'secondProtDB'=>'',
  'secondProDB_miID'=>'',
  'secondProtID'=>'',
  'protGeneID'=>'856537',
);
$intAct_array = array(
  'baitID'=>'21345',
  'baitIntActorIndex'=>'1',
  'hitIntActorIndex'=>'2',
  'prohitshitID'=>'123'
);

//if is published set 1;
$psi = new psimi_maker(1);
print $psi->makeEntrySet();
print $psi->makeSource($source_array);

print $psi->addExperiment();
print $psi->addExperimentDescription($exp_array);
print $psi->addExperiment(0);

print $psi->addInteractorList();
//loop proteins
print $psi->addInteractor($intActor_array);
print $psi->addInteractor($intActor_array);
print $psi->addInteractor($intActor_array);
print $psi->addInteractorList(0);

 
print $psi->addInteractionList();
//loop interactions
print $psi->addInteraction($intAct_array);
print $psi->addInteraction($intAct_array);
print $psi->addInteractionList(0);

print $psi->makeEntrySet(0);
*/



class psimi_maker{
  var $_xml_file_Name = '';
  var $_entrySet;
  var $_entry;
  var $_source;
  var $_experimentList = array();
  var $_interactorList = array();
  var $_interactionList = array();
  var $xml = '';
  var $published;
  var $intActNum;
    
  function psimi_maker($pub = false){
    $this->published = $pub;
    $this->intActNum = 0;
  }
  function makeEntrySet($header=1){
    if($header){
      $xml = '<entrySet level="2" minorVersion="3" version="5" xmlns="net:sf:psidev:mi" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="net:sf:psidev:mi http://psidev.sourceforge.net/mi/rel25/src/MIF253.xsd">
    <entry>';
    }else{
      $xml = '
    </entry>
  </entrySet>';
    }
    return $xml;
  }
  function makeSource($vArray){
    $xml = '
      <source release="'.$vArray['ProhitsVersion'].'" releaseDate="'.$vArray['ProhitsReleaseDate'].'">
         <names>
           <shortLabel>'.$vArray['ProhitsLable'].'</shortLabel>
           <fullName>Prohits: '.$vArray['ProhitsInstitute'].'</fullName>
         </names>';
    if(false){
      //prohits pubmed infor goes here
      $xml .= '
         <bibref>
           <xref>
             <primaryRef db="pubmed" dbAc="MI:0446" id="prohits_pubmedID" refType="primary-reference" refTypeAc="MI:0358"/>
           </xref>
         </bibref>
         <xref>
           <primaryRef db="psi-mi" dbAc="MI:0488" id="MI:prothis_mi_id" refType="primary-reference" refTypeAc="MI:0358"/>
         </xref>';
     }
     $xml .='
         <attributeList>
           <attribute name="postalAddress">'.$vArray['ProhitsAddress'].'</attribute>
           <attribute name="contactEmail">'.$vArray['ProhitsAdminEmail'].'</attribute>
         </attributeList>
       </source>';
     return $xml;
  }
  function addExperiment($header=1){
    if($header){
      $xml ='
       <experimentList>';
    }else{
      $xml='
       </experimentList>';
    }
    return $xml;
  }
  function addExperimentDescription($vArray){
     //method=MI:0096, MI:0006, MI:0007, MI:0069
     $xml='
         <experimentDescription id="1">
           <names>
             <shortLabel>'.$vArray['pubFirstAuthor'].' ('.$vArray['pubYear'].')</shortLabel>
             <fullName>'.$vArray['pubTitle'].'</fullName>
           </names>';
   if($this->published){
      $xml .='
           <bibref>
             <xref>
               <primaryRef db="pubmed" id="'.$vArray['pubmedID'].'" dbAc="MI:0446" refType="primary-reference" refTypeAc="MI:0358"/>
             </xref>
           </bibref>';
    }
      $xml .='
           <hostOrganismList>
             <hostOrganism ncbiTaxId="'.$vArray['hostTaxId'].'">
               <names>
                 <shortLabel>'.$vArray['hostOrganismLable'].'</shortLabel>
                 <fullName>'.$vArray['hostOrganismFullName'].'</fullName>
               </names>
             </hostOrganism>
           </hostOrganismList>
           <interactionDetectionMethod>
             <names>
               <shortLabel>'.$vArray['interMethodLabel'].'</shortLabel>
               <fullName>'.$vArray['interMethodFullName'].'</fullName>
             </names>
             <xref>
               <primaryRef db="psi-mi" id="'.$vArray['interMethod_miID'].'" dbAc="MI:0488"  refType="identity" refTypeAc="MI:0356"/>
               <secondaryRef db="pubmed" dbAc="MI:0446" id="7708014" refType="primary-reference" refTypeAc="MI:0358"/>
             </xref>
           </interactionDetectionMethod>
           <participantIdentificationMethod>
             <names>
               <shortLabel>ms participant</shortLabel>
               <fullName>Identification by mass spectrometry</fullName>
             </names>
             <xref>
               <primaryRef db="psi-mi" dbAc="MI:0488" id="MI:0427" refType="identity" refTypeAc="MI:0356"/>
               </xref>
           </participantIdentificationMethod>
           <attributeList>
             <attribute name="author-list">'.$vArray['pubAuthorList'].'</attribute>';
      if($this->published){
        $xml .= '
             <attribute name="journal">'.$vArray['pubJournal'].'</attribute>';
       }
       $xml .= '
             <attribute name="contact-email">'.$vArray['pubContactEmail'].'</attribute>
             <attribute name="prohits-project-id">'.$vArray['prohitsProjectID'].'</attribute>
           </attributeList>
         </experimentDescription>';
         
    return $xml;
  }
    
  function addInteractorList($header=1){
    $xml = '';
    if($header){
      $xml ='
       <interactorList>';
    }else{
      $xml ='
      </interactorList>';
    }
    return $xml;
  }
  function addInteractor($vArray){
    // db="refseq" dbAc="MI:0481" id="NP_056202.2"/>
    //db="interpro" dbAc="MI:0449" id="IPR005301"
    //db="uniprotkb" dbAc="MI:0486" id="Q7Z4Y6"
    //db="ensembl" dbAc="MI:0476" id="ENSG00000115540"/>
    //db="ipi" dbAc="MI:0675" id="IPI00398774"/>
     //db="genbank protein gi" dbAc="MI:0851" id="320647"/>
    $tmp_version_str = '';
    $tmp_geneName = ($vArray['protGeneName'])?$vArray['protGeneName']:$vArray['protID'];
    if($vArray['dbVersion']){
      $tmp_version_str = ' version="' .$vArray['dbVersion']. '"';
    }
    
 //if bait<secondaryRef else no----------------------    
    
    $xml ='
         <interactor id="'.$vArray['interactorIndex'].'">
           <names>
              <shortLabel>'.$tmp_geneName.'</shortLabel>
           </names>
           <xref>
             <primaryRef db="'.$vArray['protDB'].'" dbAc="'.$vArray['proDB_miID'].'" id="'.$vArray['protID'].'" refType="identity" refTypeAc="MI:0356"'.$tmp_version_str.'/>';
    /*if($vArray['baitID']){
      $xml .= '
             <secondaryRef db="Prohits bait" id="'.$vArray['baitID'].'"/>';
    }*/
    if($vArray['secondProtDB'] and $vArray['secondProDB_miID'] and $vArray['secondProtID']){
      $xml .= '
             <secondaryRef db="'.$vArray['secondProtDB'].'" id="'.$vArray['secondProtID'].'" dbAc="'.$vArray['secondProDB_miID'].'"/>';
    }
    if($vArray['protGeneID']){
      $xml .= '
             <secondaryRef db="entrez gene/locuslink" id="'.$vArray['protGeneID'].'" dbAc="MI:0477"/>';
    }
    $xml .= '
           </xref>
           <interactorType>
             <names>
               <shortLabel>protein</shortLabel>
               <fullName>protein</fullName>
             </names>
             <xref>
               <primaryRef db="psi-mi" dbAc="MI:0488" id="MI:0326" refType="identity" refTypeAc="MI:0356"/>
             </xref>
           </interactorType>
           <organism ncbiTaxId="'.$vArray['taxID'].'">
             <names>
                <shortLabel>'.$vArray['taxName'].'</shortLabel>
                <fullName>'.$vArray['taxName'].'</fullName>
             </names>
           </organism>
         </interactor>';
    return $xml;
  }
  function addInteractionList($header=1){
    $xml = '';
    if($header){
      $xml ='
      <interactionList>';
    }else{
      $xml ='
      </interactionList>';
    }
    return $xml;
  }
  function addInteraction($vArray){
    $this->intActNum++;
    $xml = '
        <interaction id="'.$vArray['IntActionIndex'].'">
          <names>
            <shortLabel>'.$vArray['bait_hit'].'</shortLabel>
          </names>
          <xref>
          <primaryRef db="intact" dbAc="MI:0469" id="EBI-3861494" refType="identity" refTypeAc="MI:0356" secondary="'.$vArray['bait_hit'].'"/>
          </xref>
          <experimentList>
              <experimentRef>1</experimentRef>
          </experimentList>
          <participantList>
            <participant id="'.$vArray['baitIntActorIndex'].'">
              <interactorRef>'.$vArray['baitIntActorIndex'].'</interactorRef>
              <biologicalRole>
                <names>
                  <shortLabel>unspecified role</shortLabel>
                  <fullName>unspecified role</fullName>
                </names>
                <xref>
                  <primaryRef db="psi-mi" dbAc="MI:0488" id="MI:0499" refType="identity" refTypeAc="MI:0356"/>
                  <secondaryRef db="pubmed" dbAc="MI:0446" id="14755292" refType="primary-reference" refTypeAc="MI:0358"/>
                </xref>
              </biologicalRole>
              <experimentalRoleList>
                <experimentalRole>
                  <names>
                    <shortLabel>bait</shortLabel>
                    <fullName>bait</fullName>
                  </names>
                  <xref>                 
                    <primaryRef db="psi-mi" dbAc="MI:0488" id="MI:0684" refType="identity" refTypeAc="MI:0356"/>
                    <secondaryRef db="pubmed" dbAc="MI:0446" id="14755292" refType="primary-reference" refTypeAc="MI:0358"/> 
                  </xref>
                </experimentalRole>
              </experimentalRoleList>
            </participant>
            <participant id="'.$vArray['hitIntActorIndex'].'">
              <interactorRef>'.$vArray['hitIntActorIndex'].'</interactorRef>
              <biologicalRole>
                <names>
                  <shortLabel>unspecified role</shortLabel>
                  <fullName>unspecified role</fullName>
                </names>
                <xref>
                  <primaryRef db="psi-mi" dbAc="MI:0488" id="MI:0499" refType="identity" refTypeAc="MI:0356"/>
                  <secondaryRef db="pubmed" dbAc="MI:0446" id="14755292" refType="primary-reference" refTypeAc="MI:0358"/>
                </xref>
              </biologicalRole>
              <experimentalRoleList>
                <experimentalRole>
                  <names>
                    <shortLabel>prey</shortLabel>
                    <fullName>prey</fullName>                   
                  </names>
                  <xref>
                    <primaryRef db="psi-mi" dbAc="MI:0488" id="MI:0684" refType="identity" refTypeAc="MI:0356"/>
                    <secondaryRef db="pubmed" dbAc="MI:0446" id="14755292" refType="primary-reference" refTypeAc="MI:0358"/>
                  </xref>
                </experimentalRole>
              </experimentalRoleList>
            </participant>
          </participantList>
          <interactionType>
            <names>
              <shortLabel>physical association</shortLabel>
              <fullName>physical association</fullName>
            </names>
            <xref>
              <primaryRef db="psi-mi" dbAc="MI:0488" id="MI:0915" refType="identity" refTypeAc="MI:0356"/>
              <secondaryRef db="pubmed" dbAc="MI:0446" id="14755292" refType="primary-reference" refTypeAc="MI:0358"/>
            </xref>
          </interactionType>
          <attributeList>
            <attribute name="Prohits hit ID">'.$vArray['prohitshitID'].'</attribute>
          </attributeList>
        </interaction>';
        //interactionType: physical association MI:0915
    return $xml;
  }
}
?>