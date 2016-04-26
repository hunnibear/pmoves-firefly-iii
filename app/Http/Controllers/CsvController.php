<?php
declare(strict_types = 1);

namespace FireflyIII\Http\Controllers;

use Config;
use ExpandedForm;
use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Helpers\Csv\Data;
use FireflyIII\Helpers\Csv\Importer;
use FireflyIII\Helpers\Csv\WizardInterface;
use FireflyIII\Repositories\Account\AccountRepositoryInterface as ARI;
use Illuminate\Http\Request;
use Input;
use Log;
use Preferences;
use Session;
use View;

/**
 * Class CsvController
 *
 * @package FireflyIII\Http\Controllers
 */
class CsvController extends Controller
{

    /** @var  Data */
    protected $data;
    /** @var  WizardInterface */
    protected $wizard;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        View::share('title', trans('firefly.csv'));
        View::share('mainTitleIcon', 'fa-file-text-o');

        if (Config::get('firefly.csv_import_enabled') === false) {
            throw new FireflyException('CSV Import is not enabled.');
        }

        $this->wizard = app('FireflyIII\Helpers\Csv\WizardInterface');
        $this->data   = app('FireflyIII\Helpers\Csv\Data');

    }

    /**
     * Define column roles and mapping.
     *
     * STEP THREE
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function columnRoles()
    {

        $fields = ['csv-file', 'csv-date-format', 'csv-has-headers', 'csv-import-account', 'csv-specifix', 'csv-delimiter'];
        if (!$this->wizard->sessionHasValues($fields)) {
            Log::error('Could not recover upload.');
            Session::flash('warning', strval(trans('firefly.could_not_recover')));

            return redirect(route('csv.index'));
        }

        $subTitle       = trans('firefly.csv_define_column_roles');
        $firstRow       = $this->data->getReader()->fetchOne();
        $count          = count($firstRow);
        $headers        = [];
        $example        = $this->data->getReader()->fetchOne(1);
        $availableRoles = [];
        $roles          = $this->data->getRoles();
        $map            = $this->data->getMap();

        for ($i = 1; $i <= $count; $i++) {
            $headers[] = trans('firefly.csv_column') . ' #' . $i;
        }
        if ($this->data->hasHeaders()) {
            $headers = $firstRow;
        }
        $keys = array_keys(Config::get('csv.roles'));
        foreach ($keys as $name) {
            $availableRoles[$name] = trans('firefly.csv_column_' . $name);
        }
        asort($availableRoles);

        return view('csv.column-roles', compact('availableRoles', 'map', 'roles', 'headers', 'example', 'subTitle'));
    }

    /**
     * Optional download of mapping.
     *
     * STEP FOUR THREE-A
     *
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function downloadConfig()
    {
        $fields = ['csv-date-format', 'csv-has-headers', 'csv-delimiter'];
        if (!$this->wizard->sessionHasValues($fields)) {
            Session::flash('warning', strval(trans('firefly.could_not_recover')));

            return redirect(route('csv.index'));
        }
        $data = [
            'date-format' => session('csv-date-format'),
            'has-headers' => session('csv-has-headers'),
        ];
        if (Session::has('csv-map')) {
            $data['map'] = session('csv-map');
        }
        if (Session::has('csv-roles')) {
            $data['roles'] = session('csv-roles');
        }
        if (Session::has('csv-mapped')) {
            $data['mapped'] = session('csv-mapped');
        }

        if (Session::has('csv-specifix')) {
            $data['specifix'] = session('csv-specifix');
        }

        $result = json_encode($data, JSON_PRETTY_PRINT);
        $name   = sprintf('"%s"', addcslashes('csv-configuration-' . date('Y-m-d') . '.json', '"\\'));

        return response($result, 200)
            ->header('Content-disposition', 'attachment; filename=' . $name)
            ->header('Content-Type', 'application/json')
            ->header('Content-Description', 'File Transfer')
            ->header('Connection', 'Keep-Alive')
            ->header('Expires', '0')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Pragma', 'public')
            ->header('Content-Length', strlen($result));
    }

    /**
     * @return \Illuminate\View\View
     */
    public function downloadConfigPage()
    {
        $fields = ['csv-date-format', 'csv-has-headers', 'csv-delimiter'];
        if (!$this->wizard->sessionHasValues($fields)) {
            Session::flash('warning', strval(trans('firefly.could_not_recover')));

            return redirect(route('csv.index'));
        }

        $subTitle = trans('firefly.csv_download_config_title');

        return view('csv.download-config', compact('subTitle'));
    }

    /**
     * This method shows the initial upload form.
     *
     * STEP ONE
     *
     * @param ARI $repository
     *
     * @return \Illuminate\View\View
     */
    public function index(ARI $repository)
    {
        $subTitle = trans('firefly.csv_import');

        Session::forget('csv-date-format');
        Session::forget('csv-has-headers');
        Session::forget('csv-file');
        Session::forget('csv-import-account');
        Session::forget('csv-map');
        Session::forget('csv-roles');
        Session::forget('csv-mapped');
        Session::forget('csv-specifix');
        Session::forget('csv-delimiter');

        // get list of supported specifix
        $specifix = [];
        foreach (Config::get('csv.specifix') as $entry) {
            $specifix[$entry] = trans('firefly.csv_specifix_' . $entry);
        }

        // get a list of delimiters:
        $delimiters = [
            ','   => trans('form.csv_comma'),
            ';'   => trans('form.csv_semicolon'),
            'tab' => trans('form.csv_tab'),
        ];

        // get a list of asset accounts:
        $accounts = ExpandedForm::makeSelectList($repository->getAccounts(['Asset account', 'Default account']));

        // can actually upload?
        $uploadPossible = is_writable(storage_path('upload'));
        $path           = storage_path('upload');

        return view('csv.index', compact('subTitle', 'uploadPossible', 'path', 'specifix', 'accounts', 'delimiters'));
    }

    /**
     * Parse the file.
     *
     * STEP FOUR
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initialParse()
    {
        $fields = ['csv-file', 'csv-date-format', 'csv-has-headers', 'csv-delimiter'];
        if (!$this->wizard->sessionHasValues($fields)) {
            Session::flash('warning', strval(trans('firefly.could_not_recover')));

            return redirect(route('csv.index'));
        }

        // process given roles and mapping:
        $inputMap   = Input::get('map') ?? [];
        $inputRoles = Input::get('role') ?? [];
        $roles      = $this->wizard->processSelectedRoles($inputRoles);
        $maps       = $this->wizard->processSelectedMapping($roles, $inputMap);

        Session::put('csv-map', $maps);
        Session::put('csv-roles', $roles);

        // Go back when no roles defined:
        if (count($roles) === 0) {
            Session::flash('warning', strval(trans('firefly.must_select_roles')));

            return redirect(route('csv.column-roles'));
        }

        /*
         * Continue with map specification when necessary.
         */
        if (count($maps) > 0) {
            return redirect(route('csv.map'));
        }

        /*
         * Or simply start processing.
         */

        // proceed to download config
        return redirect(route('csv.download-config-page'));

    }

    /**
     *
     * Map first if necessary,
     *
     * STEP FIVE.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws FireflyException
     */
    public function map()
    {

        // Make sure all fields we need are accounted for.
        $fields = ['csv-file', 'csv-date-format', 'csv-has-headers', 'csv-map', 'csv-roles', 'csv-delimiter'];
        if (!$this->wizard->sessionHasValues($fields)) {
            Session::flash('warning', strval(trans('firefly.could_not_recover')));

            return redirect(route('csv.index'));
        }
        /*
         * The "options" array contains all options the user has
         * per column, where the key represents the column.
         *
         * For each key there is an array which in turn represents
         * all the options available: grouped by ID.
         *
         * options[column index] = [
         *       field id => field identifier.
         * ]
         */
        $options = $this->wizard->showOptions($this->data->getMap());

        // After these values are prepped, read the actual CSV file
        $reader     = $this->data->getReader();
        $map        = $this->data->getMap();
        $hasHeaders = $this->data->hasHeaders();
        $values     = $this->wizard->getMappableValues($reader, $map, $hasHeaders);
        $map        = $this->data->getMap();
        $mapped     = $this->data->getMapped();
        $subTitle   = trans('firefly.csv_map_values');

        return view('csv.map', compact('map', 'options', 'values', 'mapped', 'subTitle'));
    }

    /**
     *
     * Finally actually process the CSV file.
     *
     * STEP SEVEN
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function process()
    {
        /*
         * Make sure all fields we need are accounted for.
         */
        $fields = ['csv-file', 'csv-date-format', 'csv-has-headers', 'csv-map', 'csv-roles', 'csv-mapped', 'csv-delimiter'];
        if (!$this->wizard->sessionHasValues($fields)) {
            Session::flash('warning', strval(trans('firefly.could_not_recover')));

            return redirect(route('csv.index'));
        }

        Log::debug('Created importer');
        /** @var Importer $importer */
        $importer = app('FireflyIII\Helpers\Csv\Importer');
        $importer->setData($this->data);
        $importer->run();
        Log::debug('Done importing!');

        $rows     = $importer->getRows();
        $errors   = $importer->getErrors();
        $imported = $importer->getImported();
        $journals = $importer->getJournals();

        Preferences::mark();

        $subTitle = trans('firefly.csv_process_title');

        return view('csv.process', compact('rows', 'errors', 'imported', 'subTitle', 'journals'));

    }

    /**
     * Store the mapping the user has made. This is
     *
     * STEP SIX
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveMapping()
    {
        /*
         * Make sure all fields we need are accounted for.
         */
        $fields = ['csv-file', 'csv-date-format', 'csv-has-headers', 'csv-map', 'csv-roles', 'csv-delimiter'];
        if (!$this->wizard->sessionHasValues($fields)) {
            Session::flash('warning', strval(trans('firefly.could_not_recover')));

            return redirect(route('csv.index'));
        }

        // save mapping to session.
        $mapped = [];
        if (!is_array(Input::get('mapping'))) {
            Session::flash('warning', strval(trans('firefly.invalid_mapping')));

            return redirect(route('csv.map'));
        }

        foreach (Input::get('mapping') as $index => $data) {
            $mapped[$index] = [];
            foreach ($data as $value => $mapping) {
                if (intval($mapping) !== 0) {
                    $mapped[$index][$value] = $mapping;
                }
            }
        }
        Session::put('csv-mapped', $mapped);

        // proceed to process.
        return redirect(route('csv.download-config-page'));

    }

    /**
     *
     * This method processes the file, puts it away somewhere safe
     * and sends you onwards.
     *
     * STEP TWO
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload(Request $request)
    {
        if (!$request->hasFile('csv')) {
            Session::flash('warning', strval(trans('firefly.no_file_uploaded')));

            return redirect(route('csv.index'));
        }

        $path                       = $this->wizard->storeCsvFile($request->file('csv')->getRealPath());
        $settings                   = [];
        $settings['date-format']    = Input::get('date_format');
        $settings['has-headers']    = intval(Input::get('has_headers')) === 1;
        $settings['specifix']       = is_array(Input::get('specifix')) ? Input::get('specifix') : [];
        $settings['import-account'] = intval(Input::get('csv_import_account'));
        $settings['delimiter']      = Input::get('csv_delimiter', ',');

        // A tab character cannot be used itself as option value in HTML
        // See http://stackoverflow.com/questions/6064135/valid-characters-in-option-value
        if ($settings['delimiter'] == 'tab') {
            $settings['delimiter'] = "\t";
        }

        $settings['map']    = [];
        $settings['mapped'] = [];
        $settings['roles']  = [];

        if ($request->hasFile('csv_config')) { // Process config file if present.

            $size = $request->file('csv_config')->getSize();
            $data = $request->file('csv_config')->openFile()->fread($size);
            $json = json_decode($data, true);
            if (is_array($json)) {
                $settings = array_merge($settings, $json);
            }
        }

        $this->data->setCsvFileLocation($path);
        $this->data->setDateFormat($settings['date-format']);
        $this->data->setHasHeaders($settings['has-headers']);
        $this->data->setMap($settings['map']);
        $this->data->setMapped($settings['mapped']);
        $this->data->setRoles($settings['roles']);
        $this->data->setSpecifix($settings['specifix']);
        $this->data->setImportAccount($settings['import-account']);
        $this->data->setDelimiter($settings['delimiter']);

        return redirect(route('csv.column-roles'));

    }
}
