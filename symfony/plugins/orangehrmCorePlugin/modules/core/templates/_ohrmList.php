<?php echo use_stylesheet('../orangehrmCorePlugin/css/_ohrmList.css'); ?>

<!-- CSS goes in the document HEAD or added to your external stylesheet -->
<style type="text/css">
/* week table */
table.week-table {
	font-family: verdana,arial,sans-serif;
	width: 100%;
	margin: 0 auto;
	border-collapse: collapse;
}
table.week-table th {
	border-width: 5px;
	padding: 1px;
	border-style: solid;
	border-color: #FAD163;
	width: 11%;
	font-size: 16px;
}
table.week-table td {
	vertical-align: top;
	text-align: center;
	width: 11%;
}
table.week-table tr {
	border-style: solid;
	border-width: 5px;
	padding: 5px;
	border-color: #FAD163;
}

/* day table */
table.day-table{
	margin: 0 auto;/*
	border-style: solid;
	border-width: 1px;*/
}
table.day-table td{
	width: 100%;/*
	border-style: solid;
	border-width: 1px;*/
}

/* headings table */
table.heading-column-table{
	margin: 0 auto;
}
table.heading-column-table td{
	text-align: right;
	min-width: 150px;
}

/* misc TD's shared by nested tables */
td.total{
	border-width: 1px !important ;
	border-top: dotted ;
	padding: 5px !important ;
	font-weight: bold;
}
td.resource-names{
	font-size: 6px;
	font-style: italic;
}
td.date-heading{
	font-size: 12px !important;
	text-decoration: underline !important;
	padding: 5px !important;
}
td.activity{
	font-weight: bold;
	font-style: italic;
}
td {
	font-size: 10px;
}
</style>

<?php

//foreach ($array as $key => $value)

	// var_dump($_POST);
	$startDate = $_POST['time']['project_date_range']['from'];
	$endDate = $_POST['time']['project_date_range']['to'];
	$projectId = $_POST['time']['project_name'];
	$timesheetDao = new TimesheetDao();
    $employeeService = new EmployeeService();
	$timesheetItems = $timesheetDao->getTimesheetItemsByDateRangeProjectId($startDate, $endDate, $projectId);

	$all = [ ];//"foo" => "bar", "bar" => "foo" ];
	$all_activities = [ ];
	$all_names = [ ];
	foreach($timesheetItems as $theitem){
		if($theitem['timesheetItemId']){
			
			// with this i want to get approved status...
			$foo = $timesheetDao->getTimesheetActionLogByTimesheetId($theitem['timesheetId']);
			
			$employee = $employeeService->getEmployee($theitem['employeeId']);
			$name = $employee->getFirstName() . " " . $employee->getLastName();
			
			$activity_name = $timesheetDao->getActivityByActivityId($theitem['activityId'])->getName();
			
			// $theitem['projectId'] -> 1
			// $theitem['comment'] -> Metting with 
			// foreach($theitem as $key => $value){ echo sprintf("\$theitem['<b>%s</b>'] -> <u>%s</u><br>", $key, $value); }
			$hours = $theitem['duration']/3600;
			$all[$theitem['date']]['day total'] += $hours;
			$all[$theitem['date']]['project activities'][$activity_name]['activity total'] += $hours;
			$all[$theitem['date']]['project activities'][$activity_name]['resources'][$name] = $hours;
			
			$all_activities[$activity_name] += $hours;
			$all_names[$name] += $hours;
		}
	}
	// now arrange by calendar weeks ==> easier for front end to render as a table...
	$startMonday = strtotime('last monday', strtotime(array_keys($all)[0]));
	$endMonday = strtotime('last monday', strtotime(end(array_keys($all))));
	
	$weeks = [ ];
	$day = $startMonday;
	while($day < strtotime('next monday', $endMonday) ){
		if(isset($all[date('Y-m-d',$day)])){
			$weeks[date('Y-m-d',$day)] = $all[date('Y-m-d',$day)];
		}
		else{
			$weeks[date('Y-m-d',$day)] = '';
		}
		$day += 86400;
	}
	
	?>

<?php
if ($tableWidth == 'auto') {
    $outboxWidth = 0;
    foreach ($columns as $header) {
        $outboxWidth = $outboxWidth + $header->getWidth();
    }
    $outboxWidth .= 'px';
} else {
    $outboxWidth = 'auto';
}

function renderActionBar($buttons, $condition = true) {
    if ($condition && count($buttons) > 0) {
?>
        <div class="actionbar">
            <div class="formbuttons">
        <?php
        foreach ($buttons as $key => $buttonProperties) {
            $button = new Button();
            $button->setProperties($buttonProperties);
            $button->setIdentifier($key);
            echo $button->__toString(), "\n";
        }
        ?>
    </div>

    <br class="clear" />

   
</div>
 <?php } ?>
<?php
}

function printAssetPaths($assets, $assestsPath = '') {

    if (count($assets) > 0) {

        foreach ($assets as $key => $asset) {
            $assetType = substr($asset, strrpos($asset, '.') + 1);

            if ($assestsPath == '') {
                echo javascript_include_tag($asset);
            } elseif ($assetType == 'js') {
                echo javascript_include_tag($assestsPath . 'js/' . $asset);
            } elseif ($assetType == 'css') {
                echo stylesheet_tag($assestsPath . 'css/' . $asset);
            } else {
                echo $assetType;
            }
        }
    }
}

function printButtonEventBindings($buttons) {
    foreach ($buttons as $key => $buttonProperties) {
        $button = new Button();
        $button->setProperties($buttonProperties);
        $button->setIdentifier($key);
        if (!empty($buttonProperties['function'])) {
            echo "\t\$('#{$button->getId()}').click({$buttonProperties['function']});", "\n";
        }
    }
}
?>

	<?php
	
	echo '<table class="week-table"><th>Activity Names</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th><th>Sun</th><th>Week</th>';

	$i = 0;
	$week_total = [ ];
	foreach(array_keys($weeks) as $day){
		
		//this is the Activity Names column
		if( $i%7 == 0 ){
			echo '<tr><td><table class="heading-column-table"><tr><td class="date-heading"><br></td></tr>';
			foreach ($all_activities as $activity_name=>$activity_details){
				echo '<tr><td class="activity">'.$activity_name.':</td></tr>';
				foreach($all_names as $name=>$names_total){
					echo '<tr><td class="resource-names">'.$name.'</td></tr>';
				}
			}
			echo '<tr><td class="total">Total: </td></tr>';
			echo '</table></td>';
		}
		
		// this is the day cell
		echo '<td><table class="day-table"><tr><td class="date-heading">'.date('M-d',strtotime($day)).'</td></tr>';
		foreach ($all_activities as $activity_name=>$activity_details){
			if($weeks[$day]['project activities'][$activity_name]['activity total']){
				echo '<tr><td class="activity">'.$weeks[$day]['project activities'][$activity_name]['activity total'].'</td></tr>';
				$week_total['activities total'][$activity_name] += $weeks[$day]['project activities'][$activity_name]['activity total'];
			}
			else{
				echo '<tr><td><b><br></b></td></tr>';
			}
			foreach($all_names as $name=>$names_total){
				// echo $weeks[$day]['project activities'][$activity_name]['resources'][$name];
				if($weeks[$day]['project activities'][$activity_name]['resources'][$name] != ''){
					echo '<tr><td class="resource-names">'.$weeks[$day]['project activities'][$activity_name]['resources'][$name].'</td></tr>';
					$week_total['resource total'][$activity_name]['resources'][$name] += $weeks[$day]['project activities'][$activity_name]['resources'][$name];
				}
				else{
					echo '<tr><td class="resource-names"><br></td></tr>';
				}
			}
		}
		if($weeks[$day]['day total']){
			echo '<tr><td class="total">'.$weeks[$day]['day total'].' hr</td></tr>';
			$week_total['all total'] += $weeks[$day]['day total'];
		}else{
			echo '<tr><td class="total"><br></td></tr>';
		}
		echo '</table></td>';
		
		// print week's total numbers
		if( $i%7 == 6 ){
			echo '<td><table class="day-table"><tr><td class="date-heading"><br></td></tr>';
			foreach ($all_activities as $activity_name=>$activity_details){
				if($week_total['activities total'][$activity_name]){
					echo '<tr><td class="activity">'.$week_total['activities total'][$activity_name].'</td></tr>';
				}else{
					echo '<tr><td class="activity"><br></td></tr>';
				}
				foreach($all_names as $name=>$names_total){
					if($week_total['resource total'][$activity_name]['resources'][$name]){
						echo '<tr><td class="resource-names">'.$week_total['resource total'][$activity_name]['resources'][$name].'</td></tr>';
					}else{
						echo '<tr><td class="resource-names"><br></td></tr>';
					}
				}
			}
			echo '<tr><td class="total">'.$week_total['all total'].'</td></tr>';
			echo '</table></td>';
			echo '</tr>';
			
			//reset for next week
			$week_total = [0];
		}
		$i++;
	}
	echo '</table>';
	
	// echo '<pre>';
		// print_r($all);
		// echo count($weeks).'<br>';
		// print_r($weeks);
	// echo '</pre>';
?>


<div class="outerbox" style="padding-right: 15px; width: <?php echo $outboxWidth; ?>">
<?php if (!empty($title)) { ?>
        <div class="mainHeading"><h2><?php echo __($title); ?></h2></div>

<?php if ($partial != null): ?>
        <div style="padding-left: 5px; padding-top: 5px;">
<?php
        include_partial($partial, $sf_data->getRaw('params'));
?>
    </div>
<?php endif; ?>

<?php } ?>

    <?php include_component('core', 'ohrmPluginPannel', array('location' => 'widget-panel-1')) ?>
    <?php include_component('core', 'ohrmPluginPannel', array('location' => 'widget-panel-2')) ?>
        
    <form method="<?php echo $formMethod; ?>" action="<?php echo public_path($formAction); ?>" id="frmList_ohrmListComponent">
<?php
    if (count($buttons) > 0) {
        renderActionBar($buttons, $buttonsPosition === ohrmListConfigurationFactory::BEFORE_TABLE);
        echo "<br class=\"clear\" />";
    }

    if (isset($extraButtons)) {
        renderActionBar($extraButtons);
        echo "<br class=\"clear\" />";
    }

    include_component('core', 'ohrmPluginPannel', array('location' => 'list-component-before-table-action-bar'));
?>
        <div id="helpText" class="helpText"></div>
        <?php if ($pager->haveToPaginate()) {
 ?>
            <div class="navigationHearder">
                <div class="pagingbar"><?php include_partial('global/paging_links_js', array('pager' => $pager)); ?></div>
                <br class="clear" />
            </div>
<?php } ?>

        <table style="border-collapse: collapse; width: <?php echo $tableWidth; ?>; text-align: left;" class="data-table">
            <colgroup align="right">
<?php if ($hasSelectableRows) { ?>
                    <col width="50" />
<?php } ?>
                <?php foreach ($columns as $header) {
 ?>
                    <col width="<?php echo $header->getWidth(); ?>" />
                <?php } ?>
            </colgroup>

                    <?php
                    
                    $headerRow1 = '';
                    $headerRow2 = '';
                    
                    if ($hasSelectableRows) {
                        $selectAllCheckbox = new Checkbox();
                        $selectAllCheckbox->setProperties(array(
                            'id' => 'ohrmList_chkSelectAll',
                            'name' => 'chkSelectAll'
                        ));
                        
                        $selectAllCheckbox->setIdentifier('Select_All');                                            
                        $selectAllRowspan = $showGroupHeaders ? 2 : 1;     
                        
                        $headerRow1 .= content_tag('th', $selectAllCheckbox->__toString(),
                                                   array('rowspan' => $selectAllRowspan)) . "\n";
                    }


                    foreach ($headerGroups as $group) {
                        
                        $rowspan = 1;

                        if ($showGroupHeaders) {
                            if ($group->showHeader()) {
                                
                                $headerCell = new HeaderCell();
                                $headerCell->setProperties(array(
                                    'label' => __($group->getName()),
                                        )
                                );
                                
                                $groupColspan = $group->getHeaderCount();
                                $headerRow1 .= content_tag('th', $headerCell->__toString(),
                                               array('style' => 'text-align: center',
                                                     'colspan' => $groupColspan)) . "\n";
                                

                            } else {
                                
                                // If we are displaying group headers and this is a
                                // group without a header, set rowspan = 2.                                
                                $rowspan = 2;
                            }
                        }
                        
                        foreach ($group->getHeaders() as $header) {
                            if ($header->isSortable()) {
                                $nextSortOrder = ($currentSortOrder == 'ASC') ? 'DESC' : 'ASC';
                                $nextSortOrder = ($currentSortField == $header->getSortField()) ? $nextSortOrder : 'ASC';

                                $sortOrderStyle = ($currentSortOrder == '') ? 'null' : $currentSortOrder;
                                $sortOrderStyle = ($currentSortField == $header->getSortField()) ? $sortOrderStyle : 'null';

                                $currentModule = sfContext::getInstance()->getModuleName();
                                $currentAction = sfContext::getInstance()->getActionName();

                                $sortUrl = public_path("index.php/{$currentModule}/{$currentAction}?sortField={$header->getSortField()}&sortOrder={$nextSortOrder}", true);

                                $headerCell = new SortableHeaderCell();
                                $headerCell->setProperties(array(
                                    'label' => __($header->getName()),
                                    'sortUrl' => $sortUrl,
                                    'currentSortOrder' => $sortOrderStyle,
                                ));
                            } else {
                                $headerCell = new HeaderCell();
                                $headerCell->setProperties(array(
                                    'label' => __($header->getName()),
                                        )
                                );
                            }

                            $headerCellHtml = '<th style="text-align: ' . $header->getTextAlignmentStyleForHeader() . '"' .
                                              ' rowspan="' . $rowspan .
                                              '">' . $headerCell->__toString() . "</th>\n";
                            
                            if ($group->showHeader()) {
                                $headerRow2 .= $headerCellHtml;
                            } else {
                                $headerRow1 .= $headerCellHtml;
                            }
                        } 
                    }
                    ?>
            <thead>
                <tr><?php echo $headerRow1;?></tr>            
                <?php if (!empty($headerRow2)) { ?>
                <tr><?php echo $headerRow2;?></tr>
                <?php } ?>
            </thead>

            <tbody>
                <?php
                    if (is_object($data) && $data->count() > 0) {
                        $rowCssClass = 'even';

                        foreach ($data as $object) {
                            
                            $rowCssClass = ($rowCssClass === 'odd') ? 'even' : 'odd';
                ?>
                            <tr class="<?php echo $rowCssClass; ?>">
                    <?php
                            if ($hasSelectableRows) {
                                $idValue = ($object instanceof sfOutputEscaperArrayDecorator) ? $object[$idValueGetter] : $object->$idValueGetter();
                                
                                if (in_array($idValue, $unselectableRowIds->getRawValue())) {
                                    $selectCellHtml = '&nbsp;';
                                } else {
                                    $selectCheckobx = new Checkbox();
                                    $selectCheckobx->setProperties(array(
                                        'id' => "ohrmList_chkSelectRecord_{$idValue}",
                                        'value' => $idValue,
                                        'name' => 'chkSelectRow[]'
                                    ));

                                    $selectCellHtml = $selectCheckobx->__toString();
                                }

                                echo content_tag('td', $selectCellHtml);
                            }

                            foreach ($columns as $header) {
                                $cellHtml = '';
                                $cellClass = ucfirst($header->getElementType()) . 'Cell';
                                $properties = $header->getElementProperty();

                                $cell = new $cellClass;
                                $cell->setProperties($properties);
                                $cell->setDataObject($object);
                                $cell->setHeader($header);

                                if ($hasSummary && $header->getName() == $summary['summaryField']) {
                                    ohrmListSummaryHelper::collectValue($cell->toValue(), $summary['summaryFunction']);
                                }
                                
                                $verticalStyle = '';
                                if (isset($properties['isValueList']) && $properties['isValueList']) {
                                    $verticalStyle = "style='vertical-align:top;'";
                                }
                    ?>
                                <td class="<?php echo $header->getTextAlignmentStyle(); ?>" <?php echo $verticalStyle;?>><?php echo $cell->__toString(); ?></td>
                    <?php
                            }
                    ?>
                        </tr>
                <?php
                        }
                    } else {
                        $colspan = count($columns);
                        if ($hasSelectableRows) {
                            $colspan++;
                        }
                ?>
                        <tr>
                            <td colspan="<?php echo $colspan; ?>"><?php echo __(TopLevelMessages::NO_RECORDS_FOUND); ?></td>
                        </tr>
                <?php
                    }
                ?>
                </tbody>
            <?php if ($hasSummary) {
 ?>
                        <tfoot>
                            <tr>
                    <?php
                        $firstHeader = true;
                        foreach ($columns as $header) {
                            if ($header->getName() == $summary['summaryField']) {
                                $aggregateValue = ohrmListSummaryHelper::getAggregateValue($summary['summaryFunction'], $summary['summaryFieldDecimals']);
                                if ($firstHeader) {
                                    $aggregateValue = $summary['summaryLabel'] . ':' . $aggregateValue;
                                    $firstHeader = false;
                                }
                                //echo tag('td', $aggregateValue);
                                echo "<td class='right'>" . $aggregateValue . '</td>';
                            } else {
                                $tdValue = '&nbsp;';
                                if ($firstHeader) {
                                    $tdValue = $summary['summaryLabel'];
                                    $firstHeader = false;
                                }
                                //echo tag('td', $tdValue);
                                echo "<td>" . $tdValue . '</td>';
                            }
                        }
                    ?>
                    </tr>
                </tfoot>
<?php } ?>
                </table>

<?php renderActionBar($buttons, $buttonsPosition === ohrmListConfigurationFactory::AFTER_TABLE); ?>
                    <br class="clear" />
<?php if ($pager->haveToPaginate()) { ?>
            <div class="navigationHearder">
                <div class="pagingbar"><?php include_partial('global/paging_links_js', array('pager' => $pager)); ?></div>
                <br class="clear" />
            </div>
<?php } ?>

                </form>

            </div>

<?php echo javascript_include_tag('../orangehrmCorePlugin/js/_ohrmList.js'); ?>

<?php
                    $assestsPath = "../{$pluginName}/";

                    if (isset($assets)) {
                        printAssetPaths($assets, $assestsPath);
                    }

                    if (isset($extraAssets)) {
                        printAssetPaths($extraAssets);
                    }
?>

                    <script type="text/javascript">

                        var rootPath = '<?php echo public_path('/'); ?>';

                        $(document).ready(function() {
                            ohrmList_init();

<?php
                    foreach ($jsInitMethods as $methodName) {
                        echo "\t{$methodName}();", "\n";
                    }

                    if (isset($buttons)) {
                        printButtonEventBindings($buttons);
                    }

                    if (isset($extraButtons)) {
                        printButtonEventBindings($extraButtons);
                    }
?>
    });

</script>
