<?php
/* Copyright (C) 2004-2011      Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *   \file       htdocs/cabinetmed/lib/cabinetmed.lib.php
 *   \brief      List of functions for cabinetmed module
 *   \ingroup    cabinetmed
 */


/**
 * Add alert into database
 *
 * @param	DoliDB		$db			Database handler
 * @param	string		$type		Type of alert
 * @param	int			$id			Id of alert
 * @param	string		$value		Value
 * @return	string					'' if OK, error message if KO
 */
function addAlert($db, $type, $id, $value)
{
    $res='';

    $sql = "INSERT INTO ".MAIN_DB_PREFIX."cabinetmed_patient(rowid, ".$type.") VALUES (".$id.", ".$value.")";
    dol_syslog("sql=".$sql);
    $resql1 = $db->query($sql,1);

    $sql = "UPDATE ".MAIN_DB_PREFIX."cabinetmed_patient SET ".$type."=".$value." WHERE rowid=".$id;
    dol_syslog("sql=".$sql);
    $resql2 = $db->query($sql);

    if (! $resql2)    // resql1 can fails if key already exists
    {
        $res = $db->lasterror();
    }

    return $res;
}


/**
 * List reason for consultation
 *
 * @param 	nboflines
 * @param 	newwidth		Force width
 * @param	htmlname		Name of HTML select field
 * @param	selected		Preselected value
*/
function listmotifcons($nboflines,$newwidth=0,$htmlname='motifcons',$selected='')
{
    global $db,$width;

    if (empty($newwidth)) $newwidth=$width;

    print '<select class="flat" id="list'.$htmlname.'" name="'.$htmlname.'" style="width: '.$newwidth.'px" size="'.$nboflines.'"'.($nboflines > 1?' multiple':'').'>';
    print '<option value="0"></option>';
    $sql = 'SELECT s.rowid, s.code, s.label';
    $sql.= ' FROM '.MAIN_DB_PREFIX.'cabinetmed_motifcons as s';
    $sql.= ' ORDER BY label';
    $resql=$db->query($sql);
    dol_syslog("consutlations sql=".$sql);
    if ($resql)
    {
        $num=$db->num_rows($resql);
        $i=0;

        while ($i < $num)
        {
            $obj=$db->fetch_object($resql);
            print '<option value="'.$obj->code.'"';
            if ($obj->code == $selected) print ' selected="selected"';
            print '>'.$obj->label.'</option>';
            $i++;
        }
    }
    print '</select>'."\n";
}

/**
 * List lesion diagnostic
 *
 * @param 	nboflines
 * @param 	newwidth		Force width
 * @param	htmlname		Name of HTML select field
 * @param	selected		Preselected value
*/
function listdiagles($nboflines,$newwidth=0,$htmlname='diagles',$selected='')
{
    global $db,$width;

    if (empty($newwidth)) $newwidth=$width;

    $out= '<select class="flat" id="list'.$htmlname.'" name="'.$htmlname.'" style="width: '.$newwidth.'px" size="'.$nboflines.'"'.($nboflines > 1?' multiple':'').'>';
    $out.= '<option value="0"></option>';
    $sql = 'SELECT s.rowid, s.code, s.label';
    $sql.= ' FROM '.MAIN_DB_PREFIX.'cabinetmed_diaglec as s';
    $sql.= ' ORDER BY label';
    $resql=$db->query($sql);
    dol_syslog("consutlations sql=".$sql);
    if ($resql)
    {
        $num=$db->num_rows($resql);
        $i=0;

        while ($i < $num)
        {
            $obj=$db->fetch_object($resql);
            $out.= '<option value="'.$obj->code.'"';
            if ($obj->code == $selected) $out.=' selected="selected"';
            $out.= '>'.$obj->label.'</option>';
            $i++;
        }
    }
    $out.= '</select>'."\n";
    return $out;
}

/**
 *  Show combo box with all exams
 *
 *  @param          nboflines       Max nb of lines
 *  @param          newwidth        Force width
 *  @param          type            To filter on a type
 *  @param          showtype        Show type
 *  @param          htmlname        Name of html select area
 */
function listexamen($nboflines,$newwidth=0,$type='',$showtype=0,$htmlname='examen')
{
    global $db,$width;

    if (empty($newwidth)) $newwidth=$width;

    print '<select class="flat" id="list'.$htmlname.'" name="list'.$htmlname.'" '.($newwidth?'style="width: '.$newwidth.'px"':'').' size="'.$nboflines.'"'.($nboflines > 1?' multiple':'').'>';
    print '<option value="0"></option>';
    $sql = 'SELECT s.rowid, s.code, s.label, s.biorad as type';
    $sql.= ' FROM '.MAIN_DB_PREFIX.'cabinetmed_examenprescrit as s';
    if ($type) $sql.=" WHERE s.biorad in ('".$type."')";
    $sql.= ' ORDER BY label';
    $resql=$db->query($sql);
    dol_syslog("consutlations sql=".$sql);
    if ($resql)
    {
        $num=$db->num_rows($resql);
        $i=0;

        while ($i < $num)
        {
            $obj=$db->fetch_object($resql);
            print '<option value="'.$obj->code.'">'.$obj->label.($showtype?' ('.strtolower($obj->type).')':'').'</option>';
            $i++;
        }
    }
    print '</select>'."\n";
}


/**
 *  Show combo box with all exam conclusions
 *
 *  @param          nboflines       Max nb of lines
 *  @param          newwidth        Force width
 *  @param          htmlname        Name of html select area
 */
function listexamconclusion($nboflines,$newwidth=0,$htmlname='examconc')
{
    global $db,$width;

    if (empty($newwidth)) $newwidth=$width;

    print '<select class="flat" id="list'.$htmlname.'" name="list'.$htmlname.'" style="width: '.$newwidth.'px" size="'.$nboflines.'"'.($nboflines > 1?' multiple':'').'>';
    print '<option value="0"></option>';
    $sql = 'SELECT s.rowid, s.code, s.label';
    $sql.= ' FROM '.MAIN_DB_PREFIX.'cabinetmed_c_examconclusion as s';
    $sql.= ' ORDER BY label';
    $resql=$db->query($sql);
    dol_syslog("consutlations sql=".$sql);
    if ($resql)
    {
        $num=$db->num_rows($resql);
        $i=0;

        while ($i < $num)
        {
            $obj=$db->fetch_object($resql);
            print '<option value="'.$obj->code.'">'.$obj->label.'</option>';
            $i++;
        }
    }
    print '</select>'."\n";
}

/**
 * Show combo box with list of banks
 *
 * @param 	nboflines
 * @param 	newwidth		Force width
 * @param	defautlvalue	Preselected value
 * @param	htmlname		Name of HTML select field
 */
function listebanques($nboflines,$newwidth=0,$defaultvalue='',$htmlname='banque')
{
    global $db,$width;

    if (empty($newwidth)) $newwidth=$width;

    print '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'" style="width: '.$newwidth.'px" size="'.$nboflines.'"'.($nboflines > 1?' multiple':'').'>';
    print '<option value=""></option>';
    $sql = 'SELECT s.rowid, s.code, s.label';
    $sql.= ' FROM '.MAIN_DB_PREFIX.'cabinetmed_c_banques as s';
    $sql.= ' ORDER BY label';
    $resql=$db->query($sql);
    dol_syslog("consutlations sql=".$sql);
    if ($resql)
    {
        $num=$db->num_rows($resql);
        $i=0;

        while ($i < $num)
        {
            $obj=$db->fetch_object($resql);
            print '<option value="'.dol_escape_htmltag($obj->label).'"';
            if ($defaultvalue == $obj->label) print ' selected="selected"';
            print '>'.dol_escape_htmltag($obj->label).'</option>';
            $i++;
        }
    }
    print '</select>'."\n";
}


/**
 *  Return array head with list of tabs to view object stats informations
 *
 *  @param	Object	$object         Patient or null
 *  @return	array           		head
 */
function patient_stats_prepare_head($object)
{
    global $langs, $conf, $user;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/cabinetmed/stats/index.php?userid='.$user->id,1);
    $head[$h][1] = $langs->trans("Month");
    $head[$h][2] = 'statsconsultations';
    $h++;

    $head[$h][0] = dol_buildpath('/cabinetmed/stats/geo.php?mode=cabinetmedbytown',1);
    $head[$h][1] = $langs->trans('Town');
    $head[$h][2] = 'statstown';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'cabinetmed_stats');

    return $head;
}


/**
*  Return array head with list of tabs to view object stats informations
*
*  @param	Object	$object         Contact or null
*  @return	array           		head
*/
function contact_patient_stats_prepare_head($object)
{
    global $langs, $conf, $user;

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath('/cabinetmed/stats/index_contacts.php?userid='.$user->id,1);
    $head[$h][1] = $langs->trans("Patients");
    $head[$h][2] = 'statscontacts';
    $h++;

    /*
    $head[$h][0] = dol_buildpath('/cabinetmed/stats/geo.php?mode=cabinetmedbytown',1);
    $head[$h][1] = $langs->trans('Town');
    $head[$h][2] = 'statstown';
    $h++;
	*/

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'cabinetmed_stats_contacts');

    return $head;
}

?>