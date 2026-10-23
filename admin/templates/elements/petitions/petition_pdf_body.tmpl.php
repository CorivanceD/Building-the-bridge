<div id="contentPdf">
    <table id="bodyPdf" style="width:180mm">
        <tr>
            <td><h1><?= $oPetition->title ?></h1></td>            
        </tr>
        <tr><td style="width:180mm" class="topBorder">&nbsp;</td></tr>
        <? if ($oPetition->getImage() != null) { ?>
            <tr>
                <td style="text-align: center">
                    <img class="petitionImg" src="<?= $oPetition->getImage()->getImageFileByReference('detail')->link ?>">
                </td>
            </tr>
        <? } ?>
        <tr>
            <td><?= $oPetition->description ?></td>
        </tr> 
        <tr>
            <td>&nbsp;</td>
        </tr>
    </table>
    <table id="reportDetail" style="width:180mm" cellspacing="0"  cellpadding="0" border="0">   
        <thead><tr><td width="10mm">&nbsp;<b>#</b></td>
                <td width="30mm"><b>Name</b></td>
                <td width="10mm" align="center"><b>Age</b></td>
                <td width="10mm" align="center"><b>Gender</b></td>                
                <td width="30mm" align="left"><b>Email</b></td>
                <td width="30mm" align="left"><b>Country</b></td>
                <td width="30mm" align="left"><b>City</b></td>              
                <td width="10mm" align="left"><b>Date</b></td></tr>
            <tr><td colspan="8" style="width:180mm" class="topBorder">&nbsp;</td></tr>
        </thead>            
        <tbody>
            <?
            $i = 1;
            foreach ($aRequiredSignatures AS $oPetitionSignature) {
                $oCustomer = $oPetitionSignature->getCustomer();
                ?>
                <tr><td>&nbsp;<?= $i++ ?></td>
                    <td><?= $oCustomer->name ?></td>
                    <td align="center"><?= $oCustomer->getAge(); ?></td>
                    <td align="center"><?= $oCustomer->gender; ?></td>
                    <td align="left"><?= $oCustomer->email; ?></td>
                    <td align="left"><?= $oCustomer->getCountry() == null ? '' : $oCustomer->getCountry()->name ?></td>
                    <td align="left"><?= $oCustomer->city ?></td>                      
                    <td align="left"><?= Date::strToDate($oPetitionSignature->date)->format('%d-%m-%Y') ?></td>
                </tr>                       
            <? } ?>
        </tbody>
    </table>
    <br>
    <table style="width:180mm">           
        <tr><td colspan="8"><h2>Summary</h2></tr>
        <tr><td class="topBorder" colspan="8" >&nbsp;</td></tr>
        <tr><td colspan="8" style="width:100%">
                <table>
                    <tr><td>
                            <b>Required signatures:</b>&nbsp;&nbsp;<?= $oPetition->requiredSignatures ?>
                        </td>
                        <td>&nbsp;</td>
                        <td>
                            <b>Total signatures:</b>&nbsp;&nbsp;<?= count($aRequiredSignatures) ?>
                        </td>                        
                    </tr>
                </table>                    
            </td>                  
        </tr>
        </tfoot>
    </table>
</div>
