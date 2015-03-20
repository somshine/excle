<?php
  require_once('lib/common.php');
  require_once('classes/PHPExcel.php');
  
  define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
  
  $objPHPExcel = new PHPExcel();
  
  $objPHPExcel->getProperties()->setCreator("KDham")
							 ->setLastModifiedBy("Kdham Admin")
							 ->setTitle("Package Booking Details")
							 ->setSubject("Export Booking History")
							 ->setDescription("This documnet generated for administration reference only.")
							 ->setKeywords("office PHPExcel php")
							 ->setCategory("booking");
  
  $arrobjBookingData = (array) $objDatabase->fetchResults('SELECT * FROM node LIMIT 100');
  
  $objColumnHeaders = current($arrobjBookingData);
  
  if( false == valObj( $objColumnHeaders, 'stdClass' ) ) {
    $_SESSION['error_message'] = 'Failed to load booking data from database, Pleae try again later.';
    header('Location: index.php?error=1');
    exit;
  }
  
  $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Id')
            ->setCellValue('B1', 'V Id')
            ->setCellValue('C1', 'Type')
            ->setCellValue('D1', 'Language')
            ->setCellValue('E1', 'Title')
            ->setCellValue('F1', 'User Id')
            ->setCellValue('G1', 'Status')
            ->setCellValue('H1', 'Created on');
  
  $intRowCount = 2;
  
  foreach( $arrobjBookingData as $objBooking ) {
    $intColCount = 0;
    
    $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueByColumnAndRow( $intColCount++, $intRowCount, $objBooking->nid )
            ->setCellValueByColumnAndRow( $intColCount++, $intRowCount, $objBooking->vid )
            ->setCellValueByColumnAndRow( $intColCount++, $intRowCount, $objBooking->type )
            ->setCellValueByColumnAndRow( $intColCount++, $intRowCount, $objBooking->language )
            ->setCellValueByColumnAndRow( $intColCount++, $intRowCount, $objBooking->title )
            ->setCellValueByColumnAndRow( $intColCount++, $intRowCount, $objBooking->uid )
            ->setCellValueByColumnAndRow( $intColCount++, $intRowCount, $objBooking->status )
            ->setCellValueByColumnAndRow( $intColCount++, $intRowCount, $objBooking->created );
        
        $intRowCount++;
  }

//  $objPHPExcel->getActiveSheet()->setCellValue('A8',"Hello\nWorld");
//  $objPHPExcel->getActiveSheet()->getRowDimension(8)->setRowHeight(-1);
//  $objPHPExcel->getActiveSheet()->getStyle('A8')->getAlignment()->setWrapText(true);

  // Rename worksheet
  //$objPHPExcel->getActiveSheet()->setTitle('Simple');

  // Set active sheet index to the first sheet, so Excel opens this as the first sheet
  $objPHPExcel->setActiveSheetIndex(0);

  // Save Excel 2007 file
  $callStartTime = microtime(true);

  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
  $objWriter->save(str_replace('.php', '.xlsx', __FILE__));
  $callEndTime = microtime(true);
  $callTime = $callEndTime - $callStartTime;

  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
  $objWriter->save(str_replace('.php', '.xls', __FILE__));
  
  $strFilePath = str_replace('.php', '.xlsx', __FILE__);
  $strFileName = str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME));
  
//  display( $strFileName );
//  display( filesize( $strFileName ) );
//  exit;
  
  header( 'Content-Description: File Transfer' );
  header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
  header("Content-type:   application/x-msexcel; charset=utf-8");
  header( 'Content-Disposition: attachment; filename=' . $strFileName );
  header( 'Content-Transfer-Encoding: binary' );
  header( 'Expires: 0' );
  header( 'Cache-Control: must-revalidate' );
  header( 'Pragma: public' );
  header( 'Content-Length: ' . filesize( $strFileName ) );
  readfile(str_replace('.php', '.xlsx', __FILE__));
  exit;
?>