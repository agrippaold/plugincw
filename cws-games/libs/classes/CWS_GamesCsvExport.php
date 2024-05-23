<?php


class CWS_GamesCsvExport
{	
	protected $folderDirectory = '';

	/**
     * Constructor
     */
    public function __construct()
    {
        $this->folderDirectory = ABSPATH . 'export';
    }


	/**
	 * Prepares and creates .csv file
	 * 
	 * @param [array] $rows
	 * @param [string] $case (`sessions` by default)
	 * @return [array] $result
	 */
	public function prepareCsv($rows, $case = 'sessions') {

		$result = ['status' => 1, 'status_txt' => 'OK'];

		$csvFileName = $case . '-export-' . strtotime("now") . '.csv';
		$csvFilePath = $this->folderDirectory;

		$array = [];

		switch ($case) {
			case 'sessions':
				$array = $this->prepareSessionRows($rows);
				break;
			default:
				break;
		}


		if (!empty($array)) {

			if (!file_exists($csvFilePath)) {
		    	mkdir($csvFilePath, 0777, true);
		    }

		    $csv = fopen($csvFilePath . '/' . $csvFileName, 'w');

		    foreach ($array as $row) {
	            fputcsv($csv, $row);
	        }

	        fclose($csv);

	        $result['csv'] = home_url('/export/' . $csvFileName);

		} else {
			$result['status'] 		= -1;
			$result['status_txt'] 	= 'Empty List !!!';
		}

		return $result;

	}

	/**
	 * Prepares an Array of data to be saved in a .csv file
	 * Case: Sessions
	 * 
	 * @param [array] $rows
	 * @return [array] $array
	 */
	public function prepareSessionRows($rows)
	{

		$array = [];

		$prepareServers = [];

		foreach ($rows as $row) {
			if (isset($row->providerId) && $row->providerId != '') {
				$totIn = $totOut = $totSpins  = 0;

				$currency = $row->currency ?? '';

				$currency = strtoupper($currency);

				if ($currency != '') {
					if (!array_key_exists($row->providerId, $prepareServers)) {
						$prepareServers[$row->providerId] = array(
							'providerId' 		=> $row->providerId,
							'providerName'		=> $row->providerName,
						);
					}

					if (isset($prepareServers[$row->providerId]['currencies'][$currency])) {

						$totIn 		= intval($prepareServers[$row->providerId]['currencies'][$currency]['totIn']) ?? 0;
						$totOut 	= intval($prepareServers[$row->providerId]['currencies'][$currency]['totOut']) ?? 0;
						$totSpins 	= intval($prepareServers[$row->providerId]['currencies'][$currency]['totSpins']) ?? 0;

					}

					$totIn += intval($row->totIn) ?? 0;
					$totOut += intval($row->totOut) ?? 0;
					$totSpins += intval($row->totSpins) ?? 0;

					$prepareServers[$row->providerId]['currencies'][$currency]['totIn'] = $totIn;
					$prepareServers[$row->providerId]['currencies'][$currency]['totOut'] = $totOut;
					$prepareServers[$row->providerId]['currencies'][$currency]['totSpins'] = $totSpins;
				}
			}
		}

		if (!empty($prepareServers)) {
			foreach ($prepareServers as $key => $server) {
                $serverTotalSpins = 0;

                foreach ($server['currencies'] as $currency) {
                    $serverTotalSpins += intval($currency['totSpins']) ?? 0;
                }

                $prepareServers[$key]['serverTotalSpins'] = $serverTotalSpins;
            }
		}

		if (!empty($prepareServers)) {
			foreach ($prepareServers as $server) {

				$totalPerServer = 0;

				$rowArray = [];

				$rowArray[0] = $server['providerName'] ?? '';
				$rowArray[1] = '';
				$rowArray[2] = '';
				$rowArray[3] = '';
				$rowArray[4] = '';
				$rowArray[5] = '';

				$array[] = $rowArray;

				$array[] = $this->getSessionsTitlesRowArray();

				foreach ($server['currencies'] as $currency => $currency_data) {
					$rowArray = [];

					$ggr = 0;

		            $ggr += $currency_data['totIn'] ?? 0;

		            $ggr -= $currency_data['totOut'] ?? 0;

		            $totalPerServer += ($ggr);

					$rowArray[0] = $currency;
					$rowArray[1] = $currency_data['totSpins'] ?? 0;
					$rowArray[2] = number_format(($currency_data['totIn'] ?? 0), 2, ',', ' ');
					$rowArray[3] = number_format(($currency_data['totOut'] ?? 0), 2, ',', ' ');
					$rowArray[4] = number_format($ggr, 2, ',', ' ');
					$rowArray[5] = number_format($ggr, 2, ',', ' ');

					$array[] = $rowArray;
				}

				$rowArray = [];

				$rowArray[0] = __('TOT SPINS', 'cws_games');
				$rowArray[1] = $server['serverTotalSpins'] ?? 0;
				$rowArray[2] = '';
				$rowArray[3] = '';
				$rowArray[4] = __('TOT', 'cws_games');
				$rowArray[5] = number_format($totalPerServer, 2, ',', ' ');

				$array[] = $rowArray;

				// Add and empty row between the servers

				$array[] = [];
			}
		}

		return $array;
	}

	/**
	 * Returns the column titles row
	 * Case: Sessions
	 * 
	 * @return [array] $array
	 */
	public function getSessionsTitlesRowArray()
	{
		$array = [];

		$array[0] = __('Currency', 'cws_games');
		$array[1] = __('Tot Spin', 'cws_games');
		$array[2] = __('Tot In', 'cws_games');
		$array[3] = __('Tot Out', 'cws_games');
		$array[4] = __('GGR', 'cws_games');
		$array[5] = __('GGR exchange Euro', 'cws_games');

		return $array;
	}


	/**
	 * Deletes all exported .csv files from the main directory
	 * 
	 * @return [array] $result
	 */
	public function deleteCsvFiles()
	{	
		$result = ['status' => 1, 'status_txt' => 'Files successfully deleted'];

		$folderDirectory = $this->folderDirectory;

		if (!is_dir($folderDirectory)) {

			$result['status'] 		= -30;
			$result['status_txt'] 	= 'Directory does not exist';

		} else {

			$directoryHandle = opendir($folderDirectory);

			while (($file = readdir($directoryHandle)) !== false) {
				if ($file == '.' || $file == '..') {
					continue;
				}

				$filePath = $folderDirectory . '/' . $file;

				unlink($filePath);
			}

			closedir($dirHandle);

		}

		return $result;
	}
}