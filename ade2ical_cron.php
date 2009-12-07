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

/* Ce script est déstiné à être lancer par le biais de cron, il récupère l'intégralité d'une catégorie d'un emploie du temps ade */
/* et sauvegarde le contenu par ressources dans des fichier séparés. Un autre script permet de construire l'ical de l'utilisateur */
/* avec les resources qui le concerne. Le repertoire data doit exister et les droits d'écriture et de lecture sont requis */

/* ------ Configuration ------ */
	$url="http://ade52-inpg.grenet.fr"; /* Sans slash à la fin ! */
	$project_id="63";
	$login="voirESISAR";
	$pass="esisar";
	$cat_name="trainee";
/* --------------------------- */

$ch = curl_init();
/* Login */
echo ("Loging in...<br>");
curl_setopt($ch, CURLOPT_URL, $url."/ade/custom/modules/plannings/direct_planning.jsp?projectId=".$project_id."&login=".$login."&password=".$pass."&resources=479&days=0,1,2,3,4&displayConfId=3");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$request=curl_exec($ch);
preg_match  ("#JSESSIONID=[a-zA-Z0-9]*#",$request,$tab);
curl_setopt($ch, CURLOPT_COOKIE,$tab[0]);
/* Toute les catégories Etudiant */
curl_setopt($ch, CURLOPT_URL,$url."/ade/standard/gui/tree.jsp?selectCategory=".$cat_name);
$request=curl_exec($ch);
/* Récupérations des données */
echo ("Récupération des données (peut être long)...<br> Semaine : ");
$request="";
for($i=0;$i<=52;$i++){
  echo (" ".$i);
  curl_setopt($ch, CURLOPT_URL,$url."/ade/custom/modules/plannings/info.jsp?week=".$i."&reset=true&order=slot");
  $request.=curl_exec($ch);

}
/* Parsage des données */
echo ("<br>Parsage des données...<br>");
//$test='<tr><td><SPAN CLASS="value">16/11/2009</span></td><td><a href="javascript:ev(158)">MA110-CM</a></td><td>lun. 16 nov. 09</td><td>Lundi</td><td>08h00</td><td>1h45min</td><td>1A Promo </td><td>CLERC A </td><td>C080 </td><td></td><td></td><td></td><td></td><td></td></tr><tr><td><SPAN CLASS="value">16/11/2009</span></td><td><a href="javascript:ev(2808)">MA210-CM</a></td><td>lun. 16 nov. 09</td><td>Lundi</td><td>08h00</td><td>1h45min</td><td>2A Promo SHN1 SHN2 </td><td>BOULAY I </td><td>A048 </td><td></td><td></td><td></td><td></td><td></td></tr>';
preg_match_all('#<tr><td><SPAN CLASS="value">([0-9/]+)</span></td><td><a[^>]*">([^>]*)</a></td><td>[^>]*</td><td>[^>]*</td><td>([0-9]+h[0-9]+)</td><td>([0-9]+h[0-9]*)[^>]*</td><td>([^>]*)</td><td>([^>]*)</td><td>([^>]*)</td><td>[^>]*</td><td>[^>]*</td><td>[^>]*</td><td>[^>]*</td><td>[^>]*</td></tr>#',$request,$tabl);

for ($i=0;$i<sizeof($tabl[0]);$i++){
  $date=$tabl[1][$i];
  $matiere=$tabl[2][$i];
  $time=$tabl[3][$i];
  $duree=$tabl[4][$i];
  $promo=$tabl[5][$i];
  $prof=$tabl[6][$i];
  $salle=$tabl[7][$i];
  //echo ("Le ".$date." ".$matiere."à ".$time." durant ".$duree." pour ".$promo." avec ".$prof." en ".$salle."\n");
  //echo ($promo);
  //var_dump($tabl);
/* Ical */
        preg_match("#([0-9]+)/([0-9]+)/([0-9]+)#",$date,$date);
        preg_match("#([0-9]+)h([0-9]+)#",$time,$time);
        preg_match("#([0-9]+)h([0-9]*)#",$duree,$duree);
        if(!isset($duree[2])){$duree[2]=0;}
        $ical="BEGIN:VEVENT\nDTSTART;TZID=Europe/Paris:".$date[3].$date[2].$date[1]."T".$time[1].$time[2]."00\n";
        $duree[2]+=$time[2];
        $duree[1]+=$time[1];
        $duree[1]+=(int)($duree[2]/60);
        $duree[2]=$duree[2]%60;
        if(strlen($duree[1])==1){$duree[1]="0".$duree[1];}
        if(strlen($duree[2])==1){$duree[2]="0".$duree[2];}
        $ical.="DTEND;TZID=Europe/Paris:".$date[3].$date[2].$date[1]."T".$duree[1].$duree[2]."00\n";
        $ical.="DESCRIPTION:\nSTATUS:CONFIRMED\n";
        $ical.="SUMMARY:".$matiere." en ".$salle." avec ".$prof." pour ".$promo."\n\nEND:VEVENT\n";
        $edt[$promo][0]="";
        array_push($edt[$promo],$ical);
}
//var_dump($edt);
/* Writing content */
if (sizeof($edt)==0){
  die("vide");
}
echo ("Suppression des entrées précédentes...<br>");
foreach (glob("data/*") as $filename) {
    unlink($filename);
} 
echo ("Ecriture des données dans le répertoire se stockage...<br>");
reset($edt);               
while (list($key, $value) = each($edt)) {
  //echo $key;
  $handler=fopen("data/".$key.".ics","w+"); 
  for($i=1;$i<sizeof($edt[$key]);$i++){
    fwrite($handler,$value[$i]);
  }
  fclose($handler);
}
echo ("Fin...<br>");
?>					