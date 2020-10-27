<?php
class PagedContentUtil {
    private $i18n = null;

    function __construct($i18n) {
        $this->i18n = $i18n;
    }
    
    function getNavigation($currentPage, $elementsPerPage, $totalElements) {
        $firstVisibleElement = min($currentPage * $elementsPerPage + 1, $totalElements);
        $lastVisibleElement = min($firstVisibleElement + $elementsPerPage - 1, $totalElements);
        $maxPage = intval(ceil($totalElements / $elementsPerPage));
        $hrefPrevious = 'href="?page=' . ($currentPage - 1) . '" id="styledButton"';
        $hrefNext = 'href="?page=' . ($currentPage + 1) . '" id="styledButton"';
        if ($currentPage <= 0) {
            $hrefPrevious = 'id="styledButtonGray"';
        }
        if ($currentPage + 1 >= $maxPage) {
            $hrefNext = 'id="styledButtonGray"';
        }
        $retStr = '<div style="width: 45%; display: inline-block; text-align: right;">
                        <a ' . $hrefPrevious . '><span style="font-size: 20px">&laquo;</span>&nbsp;' . $this->i18n->get('previousPage') . '</a>
                    </div>';
        $retStr .= '<div style="width: 10%; display: inline-block; text-align: center;">
                        ' . $firstVisibleElement . ' - ' . $lastVisibleElement . ' / ' . $totalElements . '
                    </div>';
        $retStr .= '<div style="width: 45%; display: inline-block; text-align: left;">
                        <a ' . $hrefNext . '>' . $this->i18n->get('nextPage') . '&nbsp;<span style="font-size: 20px">&raquo;</span></a>
                    </div>';
        return $retStr;
    }
}
?>
