<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Keyreport service
 *
 * Report generator. Create PDF-reports using headless chrome browser's pdf function
 *
 * PHP version 7.4
 *
 * @category   KeyReport
 * @package    keyreport
 * @author     Ralf Nickel <rn@itrn.de>
 * @copyright  2020 - Ralf Nickel - rn@itrn.de
 * @version    1.1.0
 * @link       http://www.ralf-nickel.de
 * @since      File available since Release 1.0.0
 */

require 'vendor/autoload.php';

/*
 * headless chrome
 */

use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;

/**
 * KeyReport creates pdf reports from HTML Templates
 *
 * @category   KeyReport
 * @package    keyreport
 * @author     Ralf Nickel <rn@itrn.de>
 * @copyright  2020 - Ralf Nickel - rn@itrn.de
 * @version    Release: @package_version@
 * @since      Class available since Release 1.0.0
 */

class KeyReport
{
    // define default location of the template. can be overwritten by parameter
    const KEY_TPL_BASE = 'http://localhost/rep-tpl/';

    // Headles chrome factory
    private $browserFactory;

    // json data, if submitted
    private $JSONDataJSFile;
    private $LoadJSONDataJS = '';

    // options for headless chrome
    private $browser_options = [
        'headless' => true, // disable headless mode
        //'connectionDelay' => 0.8, // add 0.8 second of delay between each instruction
        'sendSyncDefaultTimeout' => 10000,
        'userAgent' =>
        'Mozilla/5.0 (Linux; U; Android 4.0.2; en-us; Galaxy Nexus Build/ICL53F) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30', // mobil uagent
        //        'debugLogger' => '/tmp/chromeradar.log',
        'noSandbox' => true,
    ];

    // browser object
    private $browser;

    // html template file path
    private $templateFile;

    // page object
    private $browserPage;

    // seconds the browser have to wait for javascript
    private $waitForPDF;

    // seconds to wait for pdf generation
    private $sleepPdf;

    // pdf file options
    private $pdfOptions = [
        'printBackground' => true, // default to false
        'displayHeaderFooter' => true, // default to false
        'preferCSSPageSize' => true, // default to false ( reads parameters directly from @page )
        'headerTemplate' => '<div></div>', // see details bellow
        'footerTemplate' =>
        "<div style='font-size: 8px; margin-left: 50px;'>Page&nbsp;</div><div class='pageNumber' style='font-size: 8px;'></div><div style='font-size: 8px;'>&nbsp;/&nbsp;</div><div class='totalPages' style='font-size: 8px;'></div>", // see details bellow
        'scale' => 1.0,
        'paperWidth' => 8.26772, // defaults to 8.5 (must be float, value in inches)
        'paperHeight' => 11.6929,
    ];

    /**
     * __construct
     *
     * @param  string  $_tplFile path to html template file
     * @param  string  $_chartData string of JSON object
     * @param  integer $_waitPdf time to wait for browser until javascript is executed
     * @param  integer $_sleepPdf time to sleep process for waiting for pdf generation
     * @return void
     */
    function __construct(
        $_tplFile = '',
        $_chartData = null,
        $_waitPdf = 0,
        $_sleepPdf = 3,
        $_PDFOrientation = 'portrait'
    ) {
        // create browser instance at first
        $this->browserFactory = new BrowserFactory('chromium');
        
        $this->browser = $this->browserFactory->createBrowser(
            $this->browser_options
        );

        switch ($_PDFOrientation) {
            case 'portrait':
                $this->pdfOptions['landscape'] = false;
                break;
            case 'landscape':
                $this->pdfOptions['landscape'] = true;
                break;
            default:
                # code...
                break;
        }

        // set options
        $this->templateFile = $this->getAbsoluteTpl($_tplFile);

        // save json data to file
        $this->JSONDataJSFile = tempnam(getcwd() . '/data/', 'report_');

        file_put_contents(
            $this->JSONDataJSFile,
            'var JSONData = ' . $_chartData . ''
        );

        // create jquery script to include json to javascript
        $this->LoadJSONDataJS = file_get_contents($this->JSONDataJSFile);

        $this->Log('insert javascript: ' . $this->JSONDataJSFile, 'init');

        $this->waitForPDF = $_waitPdf;
        $this->sleepPdf = $_sleepPdf;

        // open page
        $this->browserPage = $this->browser->createPage();
    }

    /**
     * __destruct
     *
     * close browser and unlink temporary files on finish
     *
     * @return void
     */
    function __destruct()
    {
        $this->browser->close();
        unlink($this->JSONDataJSFile);
    }

    private function Log($msg, $context = '')
    {
        file_put_contents(
            'log/server.log',
            date('m-d-Y h:i:s') .
                ' - ' .
                strtoupper($context) .
                ' - ' .
                $msg .
                "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * get absolute tpl path. If posted json contains relative url, the base url is
     * added to variable.
     *
     * @throws Exception if template is empty
     */

    private function getAbsoluteTpl($_tplFile)
    {
        if ($_tplFile === '') {
            throw new Exception('No template file set');
        }

        if (substr($_tplFile, 0, 4) == 'http') {
            return $_tplFile;
        } else {
            error_log('Template file is ' + self::KEY_TPL_BASE . $_tplFile);
            return self::KEY_TPL_BASE . $_tplFile;
        }
    }

    /**
     * createPDF
     * Create PDF file and return Filepath
     * @return void
     */
    public function createPDF()
    {
        $pdftmp = "";

        if ($this->LoadJSONDataJS) {
            $this->browserPage->addPreScript($this->LoadJSONDataJS);
            //$this->browser->setPagePreScript($this->LoadJSONDataJS);

            //$this->browserPage->addScriptTag(['content' => file_get_contents($this->JSONDataJSFile)])->waitForResponse();

            $this->Log(
                'adding page prescript: ' . $this->JSONDataJSFile,
                'createpdf'
            );
        } else {
            $this->Log(
                'no chart data availabe: ' . $this->JSONDataJSFile,
                'error'
            );
        }

        $this->Log('navigating to page ' . $this->templateFile, 'createpdf');
        $this->browserPage
            ->navigate($this->templateFile)
            ->waitForNavigation(Page::DOM_CONTENT_LOADED);

        // wait for 3 seconds
        sleep($this->sleepPdf);

        // create temporary file for pdf
        $pdftmp = tempnam('/tmp', 'pdfreport_');
        $this->Log('create temporary file ' . $pdftmp, 'createpdf');

        $this->browserPage
            ->pdf($this->pdfOptions)
            ->saveToFile($pdftmp, $this->waitForPDF);

        return $pdftmp;
    }
}
