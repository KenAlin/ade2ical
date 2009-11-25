<?php
/*Copyright (c) 2009, Jean-Baptiste Fuzier
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
	Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	
	Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation 
	and/or other materials provided with the distribution.
	
	The name of the author may not be used to endorse or promote products derived from this software without specific prior written permission.
	
THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS 
BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE 
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT 
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
/* -------------------*/
/* Parseur pour ADE52 */
/*  jbfuzier 11/09    */
/* -------------------*/

/* Ce script permet de construire l'ical de l'utilisateur */
/* avec les resources qui le concerne */

header('Content-type: text/calendar; charset=iso-8859-1');
header("Content-Disposition: attachment; filename=edt2ical.ics");

if(preg_match("#/|\\\\#",$_GET['content'],$ta) == 1){
  die("Bien essayé !");
}
preg_match_all("#([^,]+),#",$_GET['content'],$tab);
$ical="BEGIN:VCALENDAR\nPRODID:-//Google Inc//Google Calendar 70.9054//EN\nVERSION:2.0\nCALSCALE:GREGORIAN\nMETHOD:PUBLISH\nX-WR-CALNAME:EDT2ICAL\nX-WR-TIMEZONE:Europe/Paris\nX-WR-CALDESC:\nBEGIN:VTIMEZONE\nTZID:Europe/Paris\nX-LIC-LOCATION:Europe/Paris\nBEGIN:DAYLIGHT\nTZOFFSETFROM:+0100\nTZOFFSETTO:+0200\nTZNAME:CEST\nDTSTART:19700329T020000\nRRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\nEND:DAYLIGHT\nBEGIN:STANDARD\nTZOFFSETFROM:+0200\nTZOFFSETTO:+0100\nTZNAME:CET\nDTSTART:19701025T030000\nRRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\nEND:STANDARD\nEND:VTIMEZONE\n";
for($i=0;$i<sizeof($tab[1]);$i++){
  $ical.=file_get_contents("data/".$tab[1][$i]);
}
$ical.="END:VCALENDAR";
echo $ical;
die(); /* Prevent the trackcode from the hoster to be inserted */
?>	