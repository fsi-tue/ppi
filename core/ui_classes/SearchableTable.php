<?php
class SearchableTable {
    private $i18n = null;

    function __construct($i18n) {
        $this->i18n = $i18n;
    }
    
    function createTable($headings, $dataRows, $widths, $textAlignments) {
        $lightColor = '#ffffff';
        $darkColor = '#dedede';
    
        $searchScript = '<script>
                            function searchTable() {
                                var input, filter, found, table, tr, td, color, i, j;
                                input = document.getElementById("search");
                                filter = input.value.toLowerCase();
                                table = document.getElementById("table");
                                tr = table.getElementsByTagName("tr");
                                color = "' . $darkColor . '";
                                for (i = 0; i < tr.length; i++) {
                                    found = false;
                                    td = tr[i].getElementsByTagName("td");
                                    for (j = 0; j < td.length; j++) {
                                        if (td[j].textContent.toLowerCase().includes(filter)) {
                                            found = true;
                                        }
                                    }
                                    if (found || input == "" || i == 0) {
                                        tr[i].style.display = "";
                                        for (j = 0; j < td.length; j++) {
                                            td[j].style.backgroundColor = color;
                                        }
                                        if (color == "' . $lightColor . '") {
                                            color = "' . $darkColor . '";
                                        } else if (color == "' . $darkColor . '") {
                                            color = "' . $lightColor . '";
                                        }
                                    } else {
                                        tr[i].style.display = "none";
                                    }
                                }
                            }
                        </script>';
        $table = $searchScript;
        $table .= '<input type="text" id="search" placeholder="&nbsp;&nbsp;&#128269;&nbsp;&nbsp;' . $this->i18n->get('search') . ' (' . $this->i18n->get('needsJavaScript') . ')' . '" onkeyup="searchTable()"><br><br>';
        $table .= '<table id="table" class="gridtable" width="100%">';
        $table .= '<tr width="100%">';
        $w = 0;
        foreach ($headings as &$heading) {
            $table .= '<th width="' . $widths[$w] . '%" style="text-align: ' . $textAlignments[$w] . '">' . $heading . '</th>';
            $w++;
        }
        $table .= '</tr>';
        $i = 0;
        foreach ($dataRows as &$row) {
            $color = $lightColor;
            if ($i % 2 == 1) {
                $color = $darkColor;
            }
            $i++;
            $table .= '<tr width="100%">';
            for ($j = 0; $j < count($row); $j++) {
                $cell = $row[$j];
                $table .= '<td style="background-color: ' . $color . '; text-align: ' . $textAlignments[$j] . ';">' . $cell . '</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</table>';
        $table .= '<script>searchTable();</script>';
        if (count($dataRows) == 0) {
            $table .= '<br><center>' . $this->i18n->get('noContent') . '</center>';
        }
        return $table;
    }
}
?>
