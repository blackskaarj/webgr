<?php
class Default_XXXHtmlTableCreator
{
	function __construct()
	{		
		echo $this->createHtmlTable($headings, $datasets);
	}
	
	private function createHtmlTable($headings, $datasets) {
		$content = "<table border='1'>\n";
		$content .= "<tr>";
		foreach ($headings as $heading) {
			$content.= "<th>$heading</th>";
		}
		$content .= "</tr>\n";
	    foreach ($datasets as $dataset) {
	    	$content .= "<tr>";
				foreach ($dataset as $cell) {
					$content.= "<td>$cell</td>";
				}
	        $content .= "</tr>\n";
	    }
		$content .= "</table>\n";

		return $content;
	}
	
}