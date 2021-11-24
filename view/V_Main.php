<?php
class V_Main {
    public function showResult($dataArr){
        $lengthOfDataArr = count($dataArr);
        if ($lengthOfDataArr == 0){
            return "<tr><td colspan='6'>該当データがありません</td></tr>";
        }
        $html = "";
        for ($i = 0; $i < $lengthOfDataArr; $i++){
            $html .= "<tr>";
            foreach($dataArr[$i] as $val){
                if (is_array($val)){
                    $html .= "<td><ul>";
                        foreach($val as $sub_val){
                            $html .=  "<li>" .$sub_val . "</li>";    
                        }
                    $html .= "</ul></td>";
                    continue;
                }
                $html .= "<td>" . $val ."</td>";
            }
            $html .= "</tr>"; 
        }
        return $html;
    }
}
?>