<?php

namespace App\CarrierAPI\Fedex;

use TCPDF;
use SimpleXMLElement;
use DOMDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Description of DHLWebAPI
 *
 * @author gmcbroom
 */
class FedexAPI extends \App\CarrierAPI\Carrier {
    /*
     *  Carrier Specific Variable declarations
     */

    private $_payor;
    private $_paymentType;
    private $_terms;
    private $_label_defn;

    function initCarrier() {

        /*
         * *****************************************
         * Define fields for Production/ Development
         * *****************************************
         */
        switch ($this->_mode) {
            case 'PRODUCTION':
                $this->_connection['url'] = "192.168.10.33";
                $this->_connection['port'] = 2000;
                $this->_Accounts['meterNumbers'] = array(
                    205691588 => 978666,
                    327423851 => 526564,
                    631510906 => 526938,
                    342638775 => 180565
                );
                break;

            default:
                $this->_connection['url'] = "192.168.10.34";
                $this->_connection['port'] = 2000;
                $this->_Accounts['meterNumbers'] = array(
                    205691588 => 978666,
                    327423851 => 526564,
                    631510906 => 526938,
                    342638775 => 180565
                );
                break;
        }

        /*
         * *****************************************
         * Define Carrier Specific field values
         *
         * i.e. IFS => Carrier conversion
         * *****************************************
         */

        // Label Stock Sizes
        $this->_LabelStockType['6X4'] = '6X4_PDF';
        $this->_LabelStockType['A4'] = '6X4_A4_PDF';

        // Define Services
        $this->_svc = [
            'IP' => '01', //  FedEx International Priority
            'IE' => '03', //  FedEx International Economy
            'IPF' => '70', //  FedEx International Priority Freight
            'UK48' => '26', //  FedEx Economy
            'USG' => '92', //  FedEx International Ground Distribution (IGD) (Use for all packages in an IGD or IGDD shipment)
            'UNUSED' => '06', //  FedEx International First
            'UNUSED' => '17', //  FedEx International Economy DirectDistribution Service
            'UNUSED' => '18', //  FedEx International Priority DirectDistribution
            'UNUSED' => '57', //  FedEx Europe First (Seethe FedEx Service Guide for served countries.)
            'UNUSED' => '82', //  FedEx® International DirectDistribution Surface Solutions (formerly called FedEx® Transborder Distribution Service for CA/MX)*
            'UNUSED' => '84', //  FedEx International Priority Direct Distribution Freight
            'UNUSED' => '86', //  FedEx International Economy Freight.
            'UNUSED' => '121', //  FedEx International Ground DirectDistribution (IGDD) (Use for the Create and Close of an IGDD shipment only)
            'UNUSED' => '112', //  FedEx Freight Priority
            'UNUSED' => '113' //  FedEx Freight Economy (CA to U.S. only)
        ];

        // Define Package types
        $this->_PackageTypes = ['OWN' => '01', 'CTN' => '01', 'PAK' => '02', 'BOX' => '03', 'TUBE' => '04', 'BOX15' => '15', 'BOX25' => '25', 'ENV' => '06', 'PCL' => 'PA'];

        // Define Weight Units
        $this->_WeightUnits = ['KG' => 'KGS', 'LB' => 'LBS'];

        // Define Short Weight Units for Send Shipment transaction
        $this->_SWeightUnits = ['KG' => 'KGS', 'LB' => 'LBS'];

        // Define DimensionUnit Units
        $this->_DimensionUnits = ['CM' => 'CM', 'IN' => 'IN'];

        // Define Short DimensionUnit Units for Send Shipment transaction
        $this->_SDimensionUnits = ['CM' => 'C', 'IN' => 'I'];

        // Define Payor Codes
        $this->_payor = ['SHIPPER' => 'SP', 'RECIPIENT' => 'RP', 'OTHER' => 'SO'];

        // Define Payor Codes for Duty
        $this->_paymentType = ['SHIPPER' => '1', 'RECIPIENT' => '2', 'OTHER' => '3'];

        // Define Terms of Sale
        $this->_terms = ['FCA' => '1', 'CIP' => '2', 'CPT' => '3', 'EXW' => '4', 'DDP' => '4'];

        // Define supported Label Sizes
        $this->_label_defn = [
            'A4' => ['p', 'in', 'A4', true, 'UTF-8', false],
            '6X4' => ['p', 'in', array(6, 4), true, 'UTF-8', false]
        ];

        // Define Logo
        $this->_logo = "iVBORw0KGgoAAAANSUhEUgAAAGcAAABGCAYAAADRsYpqAAAABmJLR0QA9wACAAOZ2rLmAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4AIWCh4W+CDlWQAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLmUHAAAgAElEQVR42u2dd5hlVZX2f2vvc0Plzrmrm+qmE6nJCCgqGGEGE6g4bQBBkQwzBGVMqGREHQSUKAooQXJGgoCNZLqbzjnHynXr3nP2Xt8f+9xbVQ0dsBln5nu4z3Oeuvf0iXvtld71rt2iqmz94wHTZ48qiKTfAcGDGhSHYPGiGKTPce9/3v3HbO8hZSF6nyDiK78p74cgCQGD4r1HJHl/hP97hRM+kqqAMQZVrfwOQjIYAVGDIwjOGFAfvT/C/93C6W36VAUR23MBMag6ABYtXkdcioPg1Lxv0v4ZwhHRigB6a0xFaGL5yzOv8/Fjfqi/uuZBVRVQUPHvj/A/y6yVhaHqENEgNIFnn53Jt8+/Udev7eTCXz/AFdc+qOHK5v0R3oFP9G5kWLFS4lGNQIRnn53J8edeqy2tnQwcXEtcUn56+Z0IqmeecIRg3rdt/42a48OmadysDkFAhBeee5Pjz7lRNzZ3UlVXT1cCHqWq2vCzK+7l59c9rO8P8Q4Jx1fylR6vHyIuxaMq4A2IB5OgYkEjnvrrW3z53Jt0cVuBpL4fcaGL4a6Tmqo8SbdQiIt894Lfc8VV92lZwEoQrqpudsP/m59t54g7bNbKJsuDF0JMbBA8ikEUvFEUMGpB4J6X5nPSOddrsmotH8t1sdeGNg6KN7L7gVNZc8535JXZK/Vvf5/Ly2++xX9ecicqVs/8zqcEdeGaCIoGDfw//JEthKO9U40d9jla9idGcHisFzBBMBiPUYNKGMp5jz3PE6dfrOesW8CHGwcxptbSIB6KEW7MIEbsuTN7TW2SY4/5MGuWr+eZ6XP+dPZPf3+Uc6pnn/LpkKXikQrS8P+fT+od0e7I+0V9HD2E2Zw6cS8uJJgz38I9/Zg2P/kc6//6Eh9vLrD7lDGMHV4NhQTyNZTylmxXDN2dkK8iUhg1ejBHDMwdfemv79MLLruDKIeefsLhYjCpgOR/jXnankEsm7HtPXZHJ16E1+BPsCiKEcF7H4yPsfhFC/A/Pl+7Fy7hhRlrKVblqa2rwRWK4BWyGYquSLajBGJQBY/BqqIidHU48lFErirLRT+/B0DPOP5wERW8gGzhJd7NQLwXfmN7BrN3OrH5OZs/73ti1lQ8iCBewYSbizEkKPaZZ7HXXacLF69l9spu8nV5apzShVLIZEMUF5fI5bKotSQkRFEeq6CpARMb0RZlMfmImqTIhVfcjSWjp53wCTFpXPBOgvhnmbu++Ztul3kq/978efsKWtO/dgeiNbGASbN5jyiIeuSVl0luullXL13O8nUtmPYuvFe8dUTeYLULqIJMDkoxkjiorUOLMcnKZShxGPj2DkZ0bmRAsZvuun4Uq6u54PLbufK392qIyKWybT4r/5k+YvPBficN25Y2b75/RyeYqNcK1A9hxsdzZpO54AJtW76ap19eiI8M2ciSU08shsRF7DSiml13GkLiIcrW4JMCpq4GnIe58/CnnYY57nhxL0xn9WGf0Ifrx/BfI/ahNvEsylTTXYi54MzPcfLxn5ItCeN/Kljo/Sxbi8icc8SJxzlHqVSiqqoKAXK5zHsVrbleWb/gHnuiKXPjDQvnzlnC3NWt5LMZ8t7TQUTROoxXJInRKI+vrSdatwG3fDmycRO0dOCKnVgBN28RImAlYVRc4MCWlXy8bQmrM3V8ecLncdmYH1xxN13q9LSvf1py2XLOKyRGiNLxaW3rolAoVMIWEUH9u3jxXvheWTvL1wi/BWdiJLEMHlhLlItClCoJaBT+pkBKe0eRGXOWMmveMl2waA0rlm+kvVggLnni7pi6+mpUHfV1NTSOHERj4yAmjxsre+42itqaXJpGAD4GybCteCgKiX9qFzsKxI8/stBu2EhbexG3cRO+roa2KEPWOzRxeGupNp6GVcuQFbOgvRPjfEXrdOdxcNxJZE45ITwHFrKWKYUWwPNw//F0GkttNk+X6+Dii//EgVPHc+C+k0L9RyMiPIVSiWtveEJvu+9vdHZ2YoxB0mBFRDDG4Jx7V5FTH9A2lZkxEaVSN2NGDebOG86SKBehRnFkiASUiJdfm8/9j/1d//LCLBYv3UixUMKjiMmAF4zxiFgSH4O4UHh0go0gn8/q2OH9OeyQPfmXT06VvfcYDyaTameveFV7hc3p9wixGFcCG0FdFblvfFO4+CId31FifVcXtHXQIIbOKEd7Js/o7jY+0Lka7dDyRMdhiAYMwn/4YKJLLxWamsKAKBhrcU4w4nm+YRQXjzyAHI44jiklljNO/DS77dKIEY9HMClgetk19+uFl99NfV01URRhjKkIpmz/y7WlfyRpVC0HP47WtoT99p5AbW11Wvg1RApvzF7ANTc/rY89+SobW4rk8oaqfES2rqbH2niLmDh9nqoUvTdB6/CIxsxfto43f/sQN/3pOf3CkXty+rc+I6OGNoTUXys1yr4TCoi8JhgbrJtaxUyZjDv/ezLgoov0UKO8sFTY2OGo8p5+WmBs3IIzinURikfUE+HxtRnsxPG4hgEnmBdf/I3MmqF+wTzM8y9i4gIrs/04u/GjtEsVNaVWmttizj3lSL57xmdC7VQVIYT1Cxdv5A93vEBtbS3V1Xk0Nb0W+zYTtS3RbFGvNBWsiTCmm32mjg8jYgIy8ptbH9ef//J+Vq9rp7omQ78BmYo5LPtpYyxiXBCEKt4BZAjJRKpBJkO+OkNNtaOrs8DVNz/Nq68u0it/8k3ZfdfRaYK/WfpQNuHeJ4iWg4FyfUaRmbNwv7pKm2fPZuacZbTEEVUK+7evoF67K7PQe0UENFMFY0djY9DVy/HdhTAjFEomw+ljP8qtg3enoVSgpbOdc086gu+e+gXpKUFY1IEYePjJ1/j66b/U6iiL2ugdQ9vw21LGBt+do+/Be32coAo3/Ppb8rGDpxInjst+dbdecvWD5HNZsvlM5d7e+6CtDiIxOIlBTXo9n2qSYExUqX9ZETw2RcY8xjs2tXQwefxw7rr+PBk+sr7n/Mpzhe8mvLDiTRrLiyAYdNfdsN8+WQZNmMSUiY3UZSxJUiIxUXBrXkENlghUMKUCMm8eungBdBcwGIxGeOCmwVP507CJ1HS309XWyZnf+iTnnvp5AXhm+hxa2zvAK2KD33pj9hItliQIRjxiFDFaMWc9obf7B+MgX9mSRBk8tIZJO49EBW667Sm95OoHqamupqoqR0ZM6sYN1luMV4z1xCQoUTphfKr5ttdvwYrgBLwoaIJ1ChrRMKA/s+au4YJf3Klx7N6hQBC+G4/gjGJQEiAYipDvuD2n4E47RQZNHMfuu40gqs3T5jTFlw3eeDweNTZEIqb8yhKSMBKe6zean446EOlKKHS2csbJR/CDM78kBuWm257Rq697SK3JQwquxrFj1pzliJYBWZuajQgRGzRMpVIuVzFb3cKLhi2cL332dSdFxjWOZOSQ/sydv4KLf30vmWxEJmNw4ij5dNJ6xRnFm7QEr4auri6aW9tobe+go6NAS2sHxTjBaYJYjw8gPKIxERLSFDzqPbUNWe57/FVeemPxlqM1o4JXB1ii1OYnEoJHg0F22xX9j7Nl0M8u1H2c4c18HbUrZzI4ibHeBGFqEtAAtSFyUcUDjwzYmR+N/iCdmuA6WjnrxC/wvVM/Jwr8+aG/c9r5N/Lvpx5BXW0ujVA8Gze2sGDxKjJZiAwkXvuyffqYN48Rs91Op3wNk57ifQBgd991NMZE3HznC7p+fTv9GqoDwuEsxhq8S7ASMkEHJCp0d3XSNGYIu0/ZibqGapSEDRu6ePXVRazf0Ex1fS5oGwHG8hoCGmM96iMyNktba4Gnnp2hB+4zQd7JrEUB5+xdELVEZQ3S1A9NnIQ//3sy4J77dPRfnqdr5WxESigmRa3L4UYoOyzIDeDSUXtz/6CJFIuCbmrnrFP+lfPP/JyA58GHX+Y7596oKrDPHuOC30ERNSxdsZ4Va1rJZbI4DcMRtMRU/Iv3YYA9Qntb51Zhmd5+yhsLPqkUZxMHcRwzdWKTuNjz1DOvUVdVjdhyruODuRXBqWJFUWew3nPmSYcz7bOHyMiRAxEJJj6JY+YuXs2Vv3lI73voNaprQG05j0wwJkKwqCgiBomUN+csI0kSbJTeszyhvG69TC0ieBWMKmbn8fDBQ2T8lT9X9V04FYwo4m1qEKFgIv4wdHeuGLE3azP9yBULJO3NnH36EXzvlKMF4Ppbn9cfXnoLXZ3d7DRqMI2jBocBMMGHzJq7Qjs6uuhXX1ehXZVNpGDDbLeQOKG+Ose/fGx3MplMjyZsFjQgBihhfCbVmhClhXgqJifC7lOHMXfxatatb8FkAO+CmfWCwZEYsCZC1dPV2c2Xj9yX8075fJBfWkoUgShrmTKhkYt/+FWZN2+VvrFgKTW56l7jmYBPaWMqoMLSFetpa+9kQP+GXgmzAbMNDoGqYsShWASDP2Av5A+3iHzlWJXVS0FdiiyHWtBrdYM4b8whRFhqS620dxU568R/5XunfVYArrrxEf3RhX8kW1VFJhuxU+MQGocNAAmlPRHh9ZnLEVP2DZqquqAeVEzIdyhS6ErYf48mrrroBNkqVF/WAAyQBG1PzYfHYzEocM9D0+no6qa6tgpRwbuQc0lq3tWXwETE6hgxckgIzlJrYTRUir0G/l7/umoO2m88b8xaRqY6QtSFIMEAFsQragxRFKElxbkyYGoq10TNtjUnwWJFiNUTicAhH4VbbhSdNk1ZtRqnSkYM6kHIUp3ESFykpavEf3zncL57+lGiqlx70+P6g0vvIJfPkckrXV2OyROHU1ebD7NJhO4kYcbsZWSjTA/QKAHqCGbMoWqITA7nHLtPGdkDx2wJlBTfK+kOPtIEKBybmkoRQ7E7OQwMVj2JN4g1aT6TIBisRCQ+oaoqyx/vfZr99hvLRz4wNZRHyvNdyrdUTvvmZ+TIIw4iF2WC4LxDxfTKz4IfzWaz9Kur7nGPWtb27WDfRCoojowI3oMaxXz0I/CHm4VjvqrR6lWpLwgvnVCirdNxznc+xffOOEpE4dc3PKI/uvwu8mKxeYNLwGQyTJnY2BzeSlEVFixYxcpV68lmIvAeMQbvBRWPNR5UMWpIEk824zno4F1k22UG06NBGER6oKayZgowemT/J6JsJpgbScAH7VUyqDoSPMZH1GQ9a9YVOO6ka/TQQ/bm8I/tJrtMGsPQAQ009MuGSNUoQ4fXM3R4/duyftVUiNpr8vi+eU5I7mXbwlFSfMEb1IBV8OqxHz4Uf/utIsf8m+rKFahAkiR0tMWceeIRnH/m50S8cvVNj+qPLr8bm7NURzkSHLFT+lXXMn7CyAEBHXCIwMy3lh/W2l6guroaI4pTxZhQD1Qfgg41griEmtoqFixapxq/Kc65t9VU+pQg1IBRRC0FFzNp9GAmTBjVi2XvGT1qOA311XS0dxNFGaw4Eq8h2RAFzaYwjSGfzxMnCXc98Dz3PTJdBw6qZdTIQUwe38gu44ez2+QmmTRhKAP61VXqWuVhRKhAPJXJZMpM2nI8kPqcLVUAKxU9hFB4AYtP7aKgxJgPHoLecpPoV45Rs2Yjha52zvzW4Xz/rCNFvHLtLY/q9y+7g4zNkMtYnPd4FE08Q0bUMWWn4X3uNXPOysfjxKFSdrI29XtaweoUEGtRb7no0juJ1Ws5e++boKZm0SgQoZJgEdraO7n6om/dMWHCqKNJkQmAkSP6ceBeE7njwecZ1L8B70wIUlL7L5rgJUAtqiUyUYZ+/fJ4tTRvKtC8fgmvvLIQsUJNTZU2jR7KgQeM5/DD9pD99t6VjJRpZSl0JGXlCZobgq++OJvZWr2ibAjLYaWmzjPMhnQmf/hQ+N2twqhGPnDKN/jhWZ8Vg+XXv3tMz7/oTjIZS1VVHvWeRA0GSyEpMnHiMBpqayvhcaHQyVtzl4CxGLFpCSONbHAVp64qYCxKicSQClIRazCRDdVcCRVYE2UQk8UgRCaD9wn19fXsMnnM0YKkNSyf5iOG4445RKrrMxS6Eoh8JdkOE7JnghgyeGJUBeOEqqos+boc9f3z1FZX4WJl1rwl/PqGx/nSN67S40/5L33mb7NRsUEglYlPSnQJib+pCMZvnVTYR2jSW8LpDFabztQEOexQ9OEHpPbCn4jDcPXNj+kPLr6DfN6Qz+ZQF6c3DwOeJIZ9poxFja+EyyvXtLJwxUaqIlDvUB8hongvGDUBGrFpucDHWCzWCtkoQ8ZGRMYSGUvGRmSjDJERIiPkTQYTCZlMhthlmTBmMGPGDAgTCw2AJUFbD9h3Eud88zN0FTroLDlEPVYMVjwOxahF0lpSpFm8KmoUKZenvUWsIcpCfXWOgfXVkBPue+RVvvydK/U/L7xNW1q7U62JN1MGeTt8s8PlXY1CArnLJKip4dobn9DvX3wbNvJkbTaYFxHUB/OReEd13jJ58hiRFFIBWLR0HevXNZPJZFIedvkeHm8sTj34CKMQKzgJ9QpRDdl7uhkCacSkoKOTbqwYfKK4uMSkcaPpX1ef+gABsUiKAxuTcOqJh8sPzv0iGVekpbWLuFjASxxyLAvOhFntpESURrIB3onIqCWjFkOWxEfEWAxC/37V4Ay/uPYBjj/ral2zfhNGsu8FHXcbmpU2S6GGq258TL9/6W1URXny+drgSCvmKQMY4tgxbGgDTWOG9AljZs5eoYXuOKT+4oNp8oJJcb6MGFR9at58BXvTNHsvbypS2ZwqQg7vwSKoceyya2MoQKSOWdWBloOHCKvCGSccITddfbp84kO7U0qUltZuit0eISGyGvIWETzgTIJTQUWJJSamhIgnMoGoab3FA5msof/gfjz05Ot898e3aXeiFfO1Jf7CPyyczckY19/+tF5w0e3ksgaTswgJSZpYYqQSOZaKMTuPHcawIf17YBXneG3mIoyJsNbitefBnRhUkh5cTCKMBKGV6ztboiMFzXYYAyV1VOWy7Da5UUylTCEpGBqe0xPiXFE47OA9uOnqE+X6X5won/30PjT0q6a1vUTzpk66CyWSkoawOY4qPiPSDGhE4h2+jI1pEvIbBSsweGAt9z3+dx56dDq9uwbfqWgY7bDmkLC+pch1v3uMLo0YnK0CX0J9JqDW6kFsT/lWlUkTRpDPZSoJ4MbWTuYtWkkUlW2tTRPAlKttTBrFKF4TugqeUlLASnbb9RyvqBUKXUWaGgey08jBwYWWnXM5xNXUOacTymuJqlyeww/dk09+ZHdZtno9f31htj7/0kJen7mQ1Wta6GjrAOPJZfJUZfN447FqiCRL7Et4G2FsgINsmidmjMWrcNefp+uRn95XrERbZ3zuED9YI5YuW8qGjS3U5iK8B8gCpeA0RfApj8t7yGYidpsyRnrXzBctWceadc1kc5m0oBXhfQwiRAjOKV4sKh6vMVOnjKT/gHqSUrwdvDCPSERnR4G99xjP4MENlVtrCqBqWmg0vSDsChgsHmuFsSOHstNRw2TaUR9hQ2s7s+cs5bUZS/SVV5fx6qx5rF7XSjYbkctlMJLmLF5RcYhk8M6nEaAln8sxY/4Kli5fS1PjyC3yIHa4aVOBxUvX0dpepKraVgZEbZZEY0QNUQqgluKY+tos48cO62NVZ8xepu0dJerrswFI9EnFHDovIflDKJYcAxuq+dVF35SRo4YgiU/zmK2IRgzGO1Q8kc2Sz9kUBnKIyfYkfb2g+t4vVzZJlEN2hEH96vjQAbvyof13lThxLFy2iqeenaG/veUpVq5tpbo6CsQPBU8oGHrrwEnwRxG0dnazdm0bTY3DK5Ha5kLaYeGIwBszlmqcKNVRDu8chuBzjI9QI6jGKBHOORobR9LYOKSPcN+ctajS3OuD6hEZcCnpPYTVoMWYnRobmdg0IiAFXtmu5qxeDNSgLZ7Z89dyyZX3aFEdWZsNoGiaMmi5LGETurtLNA4fzHlnHC0D+9WkmaMLrTAoNmOYOG40k8aNlA8eNJXjT/qFLl3TSrYGfGzDc6sHMog4xIcOi6S7SHNbcdtE9m2XdE0fyk7ZmYKhVCoxc/5yMpHBqMcRKoCRz+BsnJZ5IiSCUpwwafxwBtbXVpLKQqHIzHnryOUcQigHhLBbQym8lzktuZg9Jo8JTpueAd+WZqdEhbT/SxEMrS1dPPTsq3R2OXJZG8rzalOs2qemSSh0FBk6oj8nHns4Axtqwj3VhutJglGDF4Ng2GXn4XzsI3vzi+sfIFfVEBoBRFACdV81QGA4JZ/PM6B/bTq278zX3g7hmLdVFXvb+fWb2lmydFWAv1VTHpfgvQOTdlobxXvFWGHSxMZK0iUCS5evY/mK1T01GQfGCKq+V7gewnFrLbvtOnbcu2GESp9jfVq0g/4Dahk6ZAgb160nm4b9AZpRMhoiRm+V2lwVne3dvDlrMePGDAqlE+0pnJU1TTWcu2h5AG4zzlEyEeo8xoZgyBsXvHEJqvrXMnxg3XuZ56Qdar3M/Jz5K9iwvp1MJuoD3xtbFoBUmCtV+YhdJowSJB14hRmzl9DW0Y2YqFcwkITSbpkihCVJEmrr8uwyZeSiHaXaqiqDBjcwsKGGWA1ICVGHuqDNsSiJaApOO5w4rr7hEV2+ZlNAOspRBOGvVejq6ODnv71XH3/uDWpqc5TSsmZGwKc+JaOCJ6LklJ0bBzF69NAdNWvvwJ6s2ArPzDkrtVBMaMiD90EbKiViJIXglTiOGTG4PxOahqYxfRDkrLkrtRR78vnA/HkbV0BDlNdditlt3ChGDhsYalHi/gEGv6ncd0BDLftOHcPrMxdhq6twBCFZH6HG40kQyWLVUJuv47VZyznu5Kv0i5/5IBMmjpCGmhyIp62jm7fmrdLHnniTp6bPorYqj9WIxAjGxTgT8ESPw2hgk8ZFz6cO3a3CZdihaK1CHZXeCz8EYHDW3JXBXouk8H4JIUpRZa10rzmnjN9pGCOGDkjbGqGzq5vZc1f2JIzG4JIeGpT0xLW42DF5wmj61da8zbRut23rlfuIEY74xL5y8x3PaSlJMJHFEKEpum0REo3TpDShui7Pq28t45WZt1BXn9XabBXqhc44pqOtE4ehviZPJBHOeCJNQLIkxhO5UOrwNqZlU4G9dm3k80d8QMoUqi0JaDsXiQgaoT3SAqClrZt581YQZd7OxAzEuh5HlyQJu04aHdgy6f5Va1uYv2gNUdamSEEg66kPBasKguATRGC3yWMCJNTHzL4braeHRKFw8P5T+OwR+7KxvT2YKic4k+ANOInSZ0gzfZdQV11DXU01pW5Dc1s3Gzu6KJUSampzNNTmsVEZCAU0S2IDQcSbwB3o6oKafIZzzvqMDOxfn65ysuXWE7MdUWgvU+P77F+6dB0r1zRjrYD3IQzWMuE86SUsIYoMU3ffSXrPkkXL17KhuZMoI1jKlUe3WVNSj7/ZY1KjeMp43bt3mSHAlJSqFFg45570r7LXxEaaN7QjxhOpR1xaGvARkQqJuOAT03vmckrWZsjncuSzAagtIwBOPYrF2RJRkloUNXQXu5A45gdnH8XHP7RnupCT3/GAoKeq6PucunjJGtrbuiqRWsCobKXKVyYBurhIXV0NOzcN72Ni5s5fpu0dxQpsoynfrbx2jqrD4/BOGDSgjtEj+1f6iET6BibvVkhlptWY0cP41cUnyB5TxrJ2UwvFBGyUMnisw5uU2ZmSN4z1oFmwijoNJQSxePEIGaxEoTypBrUGXyqydkMr9fX1XPGzb1x8/LTDxKj0CqB3UDg9BOsoQPcIgmfmvFUa44iMDTiIsaiEpN2owaTh5bq2dvbZbQxjRg2uqJ33njdnLMcYTzal3RoTITYKBTSJERtGsL2tyMH7TGLEiCGVfKXPwkebLWugm/0Ntt312SflLn91TN11LH/47ely7Bc/iPee9c3dFJOUB60m0EJSBNs7W9FaYx2JTSpdBaZM8zVCKXa0tjZTdJ6jjtyfO687U4753EHnhmumRcxtDP82A4Jgo3uyPYOA8bR1FXnhxbdoae3AJWUhpt3XaX6jxqElwx4TxvLvJx8l1flcSviGDS0dTH9lIV3dRXSjpEFDjJg0kHBhAjjt5uADmjj1pM+GDEUEpzFGor4vl5YLpOJcJKUWB+435TKx2D6IQfn3yKG1XPnT4+Twj+/H7+94Xl94eR4bm1uwUk02I9jIY6NwzcA5CyG1xOBQPDFJklCKg38ZNLAfHz90H75y+IFyyId2IROlkzytQ8s7LDL49rF/l7ZB8XiFjvZuxuz57W2e/NPzvsS/HL6/NA4fGDqtJXDF1m9oZef9T9nm+Zf95Gt89vADZGB9VWgGEo9XG4TYRxPSRS76NLsEH9jblDrAph3kZWGGcF8q13Gx560Fq3l2+myd/vIcli5dy/oNXXR0F+kuFVJSoEVUAEcmk6GhropBg2qY2DSafadO4OADdpbJ40cFmIkyadpWzH+FeCI7IJxQtTC9MKoE1VCRpJyLlW/4DjfzuJTZlVKSvAHp7fS3ADqm5YiycvdAG+UlK+mJILdAUgkM8J530F5IuGzeTNbnGj1dAwhsam5n1doW1qxvYeOm1iZVbTIiT4ScyTBwUD0jhvZj9PChVFdlK7lUSDcchszb3wHDtqDB7dacCp5WXoIlpT8o2otWFwaizDdQnyBpr4pTiFKGvoZUAyv0LLMi7/DwuFT9bfo3qpgGSYXnsRiVHpzNa6/ZGtZ8k8os2izn2bzVL51w4b69l7DYwvl98MYejVCVoFUV7CjZzIP47XL578KsldkvIVkIM8+kg9ajomHgfVqFJH1gUzGHptIeYgJkIjYVUHpc2qBU0VpvUuK39PJrtkJbLff6V3IYk+73PkyMst+UcuIceJ8OUhot6To/geUTeo9sMHAmnTi9TZGXSieaSE87jCDgE0SilOuXdrxJLx+4OaFQt64622HWSGvmaeUwMM96tEUhTmKijO0z03w64IIJGiRpZ7KPUhyJE3sAAAiVSURBVCGm3WzpwnohUzaVwSgjA2Wqs5KkSIRNi1i+V8dZODMxadeN+goU5CQUs1WDmYuEMIhYYuOwGtonTbqAVhkALRPBVG3qY1yFEqZlvdJyZJEi1GRSOXicNxjjw3tqBnCpMMvjFs4zW3E62wylJW0LqfCsvKnUt2bNXUG/8V/VwZOO0/7jvq6PPzszvbFLnWUP9bfSvVn5btISsQWiSleYSgxGcUa478EX6D/uq7p6U3Mq3NR3mbJps2l0GCi9FsF40uPAzVxEYiMt2khLUaQuCt+TO+8agAiZdOFYW44HbKAbi2jgzUjolA7dCxbRUBoQ7Y12hwkSBBNoUt6DNaRheAYRH1o/NErFGkRskB5f/A5Ksl0IQaUWD8QmaNGz0+dw0Ke/qwBfO+ajAnDkMZfow0++lnIGSF+m5y1UbcUcBPPYK6iSnv4g1GCdp7WrG4DqbKY80YJZIVy70kOkQZe9iytVTo+QTJ1MVt2Mt71Tv/4DVNJILu3E8xo0qhy09DyPQYxLS9m+RyJxjE8jQ3W+V+VVKqVvfNlcBshKyms+uB5UvuIqtgahb23zqqjXAE14paujk4amadrQNE2XrWhBVVmxYgMNTdP01rv/ijrl6emzKsfcfNuTdBdjvCqPP/MGDU3TtNAVs2rtJhqapumrry+kuaWNS351D3feN52Gpml6z8N/5/rb/8JDj76MqvLWnBWV691611MkTikWYy6/9s/86d6/0tA0Ta+55VGc+vC86igYqwVj1a1el3POEauSOKXU2kbBWPV33IZXJb7jznDs7DnEf763cl7BWC3deF04Zv16un/8Q9wtt1AwVrvvuR8/b16fY11SwnkleeqJPvt15WpidSSPPFzZ73/4I3T9hhQ79BU6ce9t+wTjgnB8CkL+5flZVI/9N735j0+nCy4E6lIpKeIT5cHHXmHg5GO1PJgDJ3xVr73xUVSVY759OQ1N0zSOi9z90Is0NE3Tv786j5demVcZ/IamafrgEy/T0DRNL7zyzyxduYaGpmn6lROv4Lyf3U5D0zR98PEX2dDSTkPTNB21x4npOa+mz+0oGFseiHW9BmqWv+TSrCt2VQapeNVVPULcuOmIgrGagMannFTZnzz3PPH05/oMePHW31V+x2ecRsFYLf3xduI33gyCP/po4u9/N3z/7Q2UXnyegrHqvvZV3FlnUTBW45tv2qpwtsOsBfMT1l4L9nXBgjXUZjNM3nl4ajNtcLY2i3Mxx3z7Sk2KMatmXi/NC2+Qg/fbRc6+4Pfa2lbgwcde1y9+7kCJbJYZM5cAsPduTcxduAqAqy87QZoX/k5y2fBo++8/kRtue45sJBzx8X0O+9gHdwfgkWdmsmBBKDecfOzHpHnBzfKpQ/cIyV5qrrKDhwAM7vU6U3w+F5tsFdmXpof47ZRTNfriFyTq7hS6Ox8AcF8/Vuwv/4vozTcFIJnxMtGcUOPL/fIXYl0s0Wuzwvhce7UkHzosFNMeehTb1g5A8c671Ezai+j1V4RvfgOzen0wki3t+L33I3rjDbGfP2r7KoNb3bzifI95u/m2p6jZaZr+9cXZ+HTNzjvu/RsNTdN04fxVNDRN0x9ccjveh/PL2rBo4TIamqbpH+/5K77XflVX+V6Ku1BVLr/2fhqapmnZXPbejj39Vzz0yCuV/YuXbuyj2b01p3TjDemsdBWtcurR117D9+sXtOD1l1GvlJ7+S5jdN96IeiV+5qkw82+5nfikoEm+ua1JNcZ/8hN9NKlgrLrfXE+iirvrnr7mbv4C1CVvP37pkh3THCrxfxqyi2fvvSeSzwhHfPlnOuOt5dx+93OHnXD6VQowcHhgcl55zYO6bmMr1//uEYqxcu1l3xq3eNkGAKqqqnjgkZcolkqcf/rnpFjquVcmqgLgxxf/SQGGD6vl347+kAC8+OiF8tCt58u4EUP5wH4TKucMH9JQjrfxYonDa2lWHbJmLe7JJ3H3P4C7936SFavwSxbSvfc+KjWho8zvtX8wEPPTCvhzz+FWrCD5yGEKEB+wL8nV1wSv3a9ukdcIGT02gJNvvCGZZ5+TzLnniT9of/wJx5IsW4xZtkSik04Mmvf6K8QnnkD2ql+KXbJYom98TQDc/IVb50JsW3NcOiN9oDilM/DGW59mp31O1mG7HKcjdvuWNjRN09XrmvGqPPSXl/rM9u/97Pc4p8x4a/HbtOCZ52bQ0txBQ9M0vfq6x1B1dHQWaGiapt8551pUHW8tWNPnvJPPu64STJz1nzdXtFd9jDqP931naWnQIHWDB6ofPExL11xb+bfSW28R//u5QcOWLCY588y3ze7SAw/gVywPGvXjnwRr4BU/b0GfY5OvH0e8YS3uRxf02e+/fSK+pZnS2f/eV5tOO5Wku7jVsd8+hKAn16r4IRFh0bKVLF3ZzMC6OnadPAJjbZqBCytWbGDOkpUM7t/AHlPGpgmcZ/bc1bS0tbFz0zA2thQZM2IQxnpmL1nN2CFDqW/I4L1h/qKV1DXUMmJwA4KwsbmdN95aSm1dhv32mIiqMnvecgb1q2XI0AFpk1NghlrnYekifMsmJMpU2jYwhmTwIKIlK2HUMNzo0cimZsyCJbDTSLqHjdDo6C8Jv7gMXn0N2WkCZuI46OxC5s9BR49BBvZPE1SL37QJnf4CWt+f6IAD0Cj4XmbOJFown2TwcOSAfVP+N/iZbxAtWIaOGILfb0+E7NZpd9vlc7Z7c71s+3uzvZMtrtRW0ihxa3Z729cOz1vWjuTHPwo87S3cv3yfLd2rvH97n8Wp3+I15b1arnFL62O+V9fe/HrbWjD13S7g6ltaGksDBy21y5dKZsRIygCOvMv33nyBiq0t0rr5+W/rkvhnrqX5v/VTASt6La0l+AoM9D/1ef+/6cCndaHeJBbBpRXS94Xz/ue9KVO/b9b+eZ//B3bST2Y3z/xnAAAAAElFTkSuQmCC";

        //##################################################
        //#
        //#  Define Known fields for Transaction 020
        //#  Format is:
        //#
        //#     FieldNo/Classification/Multiplier
        //#
        //#     FieldNo : The Field no Fedex use to identify the field
        //#     Classification : DryIce, Dangerous goods etc.
        //#     Multiplier : 0 - no multiplier, 100 - multiply by 100
        //#
        //# Sample UK Transaction
        //#
        //# 0,"020"4,"IFS Global Logistics"32,"Daryl Shannon"5,"IFS Logistics Park"6,"Seven Mile Straight"7,"Antrim"8,"Antrim"117,"GB"9,"BT41 4QE"183,"02894464211"10,"205691588"12,"caroline murphy"13,"118 rickmansworth road"15,"watford"16,"herts"50,"GB"17,"WD18 7JG"18,"07852126201"1174,"N"1331,"N"24,"20160211"25,"rrererer"1086,"10"1274,"26"72,"SP"1090,"UKL"74,"GB"75,"KGS"112,"20"116,"2"1273,"01"23,"1"43,"0"79-1,"errererer"57-1,"1"58-1,"1"59-1,"1"1670-1,"100"99,""
        //#
        //##################################################

        $this->karr ['TxType'] = "0/RESPONSE/0"; // ??
        $this->karr ['TransactionID'] = "1/GENERAL/0"; // OK
        $this->karr ['Mawbs'] = "29/RESPONSE/0"; // ??
        $this->karr ['URSA'] = "30/RESPONSE/0"; // ??
        $this->karr ['commit_code'] = "33/RESPONSE/0"; // ??
        $this->karr ['Barcode_1D'] = "65/RESPONSE/0";
        $this->karr ['Barcode_1D'] = "664/RESPONSE/0";
        $this->karr ['station_id'] = "195/RESPONSE/0";
        $this->karr ['location_id'] = "198/RESPONSE/0";
        $this->karr ['tracking_form_id'] = "526/RESPONSE/0";
        $this->karr ['master_form_id'] = "1124/RESPONSE/0";
        $this->karr ['ursa_prefix'] = "1136/RESPONSE/0";
        $this->karr ['MeterNumber'] = "498/RESPONSE/0";
        $this->karr ['Pre_Assign_Flag'] = "1221/GENERAL/0"; // OK
        $this->karr ['Pre_Assign_TxIDs'] = "1222/GENERAL/0"; // NOT IN USE YET
        $this->karr ['OriginID1'] = "1084/GENERAL/0"; // OK
        $this->karr ['TxID'] = "1123/GENERAL/0"; // ??
        $this->karr ['IRS_EIN'] = "1139/IRSEIN/0"; // ??
        //$this->karr ['ThermLabInd'] = "1282/GENERAL/0"; // OK
        $this->karr ['CustLabFlag'] = "1660/GENERAL/0"; // OK
        $this->karr ['Barcode_2D'] = "3064/RESPONSE/0"; // ??
        //
        // Shipper Details
        $this->karr ['Shipper.Contact.CompanyName'] = "4/GENERAL/0"; //OK
        $this->karr ['Shipper.Contact.PersonName'] = "32/GENERAL/0"; //OK
        $this->karr ['Shipper.Address.AddressLine1'] = "5/GENERAL/0"; //OK
        $this->karr ['Shipper.Address.AddressLine2'] = "6/GENERAL/0"; //OK
        $this->karr ['Shipper.Address.City'] = "7/GENERAL/0"; //OK
        $this->karr ['Shipper.Address.County'] = "8/GENERAL/0"; //OK
        $this->karr ['Shipper.Address.CountryCode'] = "117/GENERAL/0"; //OK
        $this->karr ['Shipper.Address.PostCode'] = "9/GENERAL/0"; //OK
        $this->karr ['Shipper.Contact.PhoneNumber'] = "183/GENERAL/0"; //OK
        $this->karr ['Shipper.Contact.Account'] = "10/GENERAL/0"; //OK
        //
        //Receiver Details
        $this->karr ['Recipient.Contact.PersonName'] = "12/GENERAL/0"; //OK
        $this->karr ['Recipient.Contact.CompanyName'] = "11/GENERAL/0"; //OK
        $this->karr ['Recipient.Contact.PhoneNumber'] = "18/GENERAL/0"; //OK
        // $this->karr ['Recipient.Contact.Email'] = "177/GENERAL/0"; //OK
        $this->karr ['Recipient.Contact.Account'] = "177/GENERAL/0"; //OK
        $this->karr ['Recipient.Address.AddressLine1'] = "13/GENERAL/0"; //OK
        $this->karr ['Recipient.Address.AddressLine2'] = "14/GENERAL/0"; //OK
        $this->karr ['Recipient.Address.City'] = "15/GENERAL/0"; //OK
        $this->karr ['Recipient.Address.County'] = "16/GENERAL/0"; //OK
        $this->karr ['Recipient.Address.CountryCode'] = "50/GENERAL/0"; //OK
        $this->karr ['Recipient.Address.PostCode'] = "17/GENERAL/0"; //OK
        //
        // Shipment Level Details
        $this->karr ['ShipDate'] = "24/SHIPMENT/0"; //OK
        $this->karr ['Reference'] = "25/SHIPMENT/0"; // OK
        $this->karr ['Instructions'] = "3021/SHIPMENT/0"; // OK
        $this->karr ['Service'] = "1274/SHIPMENT/0"; //OK
        $this->karr ['PackageCount'] = "116/SHIPMENT/0"; //OK
        // *** $this->karr ['Adm_Type'] = "1958/ADM/0"; // Admissibility Package Type - customers own packaging Always set to BOX (is still required?)
        $this->karr ['PackagingType'] = "1273/SHIPMENT/0"; //OK
        $this->karr ['WeightUnits'] = "75/SHIPMENT/0"; //OK
        $this->karr ['DimensionUnits'] = "1116/SHIPMENT/0"; //OK
        $this->karr ['TotalWeight'] = "112/SHIPMENT/0"; //OK
        $this->karr ['TotalVolWeight'] = "1086/SHIPMENT/0"; //ok
        $this->karr ['CountryOfDestination'] = "74/SHIPMENT/0"; //OK
        $this->karr ['PayorFreight'] = "23/SHIPMENT/0"; //OK - Sender/ Recipient/ Thirdparty
        $this->karr ['PayorDuty'] = "70/PAYMENT/0"; //ok
        $this->karr ['CustomsClearanceDetail.TermsOfSale'] = "72/SHIPMENT/0"; //OK - Required for commercial invoice
        $this->karr ['CustomsClearanceDetail.DutyDetails.DeclaredCurrency'] = "68/SHIPMENT/0"; //ok
        $this->karr ['CustomsClearanceDetail.DutyDetails.DeclaredValue'] = "119/SHIPMENT/0"; //OK
        $this->karr ['CurrencyCode'] = "1090/SHIPMENT/0";
        // $this->karr ['InsuranceAmount'] = "69/SHIPMENT/0"; //ok
        // *** $this->karr ['meterNumber'] = "498/SHIPMENT/0"; //OK
        //
        // Package Level data
        $this->karr ['Packages.Package.*.SequenceNumber'] = "1117/PACKAGE/0"; //OK
        $this->karr ['Packages.Package.*.Weight'] = "1670/PACKAGE/0"; //OK
        $this->karr ['Packages.Package.*.Length'] = "59/PACKAGE/0"; //OK
        $this->karr ['Packages.Package.*.Width'] = "58/PACKAGE/0"; //OK
        $this->karr ['Packages.Package.*.Height'] = "57/PACKAGE/0"; //OK
        //
        // Email Notification - Performed at IFS Level
        //                      Options
        // DryIce
        $this->karr ['DryIce.DryIceFlag'] = "1268/DRYICE/0"; // OK
        $this->karr ['DryIce.DryIceWeight'] = "1684/DRYICE/0";  //OK
        // Alcohol
        $this->karr ['Alcohol.AlcoholFlag'] = "1332/ALCOHOL/0"; //OK
        $this->karr ['Alcohol.AlcCont'] = "40/ALCOHOL/0"; //OK
        $this->karr ['Alcohol.AlcPkg'] = "41/ALCOHOL/0"; //OK
        $this->karr ['Alcohol.AlcVol'] = "42/ALCOHOL/0"; // OK
        $this->karr ['Alcohol.AlcQty'] = "52/ALCOHOL/0"; // OK
        //
        // $this->karr ['CustomsClearanceDetail.FreeCirculation'] = "1097/OPTION/0"; //OK
        // Options - Details in Options function
        $this->karr ['SpecialServices'] = "0/OPTION/0"; // OK
        //
        // Broker Fields
        $this->karr ['BrokerSelect'] = "1174/GENERAL/0"; //OK
        $this->karr ['Broker.Contact.Identifier'] = "1187/BROKER/0"; //OK
        $this->karr ['Broker.Contact.PersonName'] = "66/BROKER/0"; //OK
        $this->karr ['Broker.Contact.CompanyName'] = "1180/BROKER/0"; //OK
        $this->karr ['Broker.Contact.PhoneNumber'] = "67/BROKER/0"; //OK
        $this->karr ['Broker.Contact.Email'] = "1343/BROKER/0"; //OK
        $this->karr ['Broker.Contact.Account'] = "1179/BROKER/0"; //OK
        $this->karr ['Broker.Address.AddressLine1'] = "1181/BROKER/0"; //OK
        $this->karr ['Broker.Address.AddressLine2'] = "1182/BROKER/0"; //OK
        $this->karr ['Broker.Address.City'] = "1183/BROKER/0"; //OK
        $this->karr ['Broker.Address.County'] = "1184/BROKER/0"; //OK
        $this->karr ['Broker.Address.PostCode'] = "1185/BROKER/0"; //OK
        $this->karr ['Broker.Address.CountryCode'] = "1186/BROKER/0"; //OK
        //$this->karr ['BR_Fax_No'] = "1344/BROKER/0";
        //$this->karr ['BR_Pager'] = "1345/BROKER/0";
        //
        // Document Fields
        $this->karr ['Documents.Content'] = "2396/DOCUMENT/0"; // OK
        $this->karr ['Documents.DocFlag'] = "190/DOCUMENT/0"; // OK
        //
        // Commodity Fields
        $this->karr ['CustomsClearanceDetail.Commodities.Commodity.*.Weight'] = "77/COMMODITY/0"; //OK
        $this->karr ['CustomsClearanceDetail.Commodities.Commodity.*.Description'] = "79/COMMODITY/0"; //OK
        $this->karr ['CustomsClearanceDetail.Commodities.Commodity.*.CountryOfManufacture'] = "80/COMMODITY/0"; // OK
        $this->karr ['CustomsClearanceDetail.Commodities.Commodity.*.HarmonizedCode'] = "81/COMMODITY/0"; //OK
        $this->karr ['CustomsClearanceDetail.Commodities.Commodity.*.QuantityUnits'] = "414/COMMODITY/0"; //OK
        $this->karr ['CustomsClearanceDetail.Commodities.Commodity.*.UnitPrice'] = "1030/COMMODITY/0"; // *** IS THIS VALUE CORRECT? SEE 020 OUTPUT ***
        $this->karr ['CustomsClearanceDetail.Commodities.Commodity.*.PartNumber'] = "1275/COMMODITY/0"; //OK
        $this->karr ['CustomsClearanceDetail.DutyDetails.DeclaredValue'] = "78/COMMODITY/0"; //OK
        $this->karr ['CustomsClearanceDetail.DutyDetails.ExportLicense'] = "83/COMMODITY/0"; //OK
        $this->karr ['CustomsClearanceDetail.DutyDetails.ExportLicenseDate'] = "84/COMMODITY/0"; //OK
        //
        // Dangerous Goods Flags
        $this->karr ['DGoods.DgFlag'] = "1331/GENERAL/0"; //ok
        $this->karr ['DGoods.Class'] = "492/DGOODS/0";
        $this->karr ['DGoods.Excep_Qty'] = "1669/DGOODS/0"; //ok
        $this->karr ['DGoods.Comm_Cnt'] = "1932/DGOODS/0";
        $this->karr ['DGoods.Name_Sig'] = "1918/DGOODS/0";
        $this->karr ['DGoods.Place_Sig'] = "1922/DGOODS/0";
        $this->karr ['DGoods.Title_Sig'] = "485/DGOODS/0";

        // *** NONE OF THESE DANGEROUS GOODSFIELDS ARE BEING CAPTURED *** //
        /*

          $this->karr ['DGTech_Name'] = "446/DGOODS/0";
          $this->karr ['DGUN_no'] = "451/DGOODS/0";
          $this->karr ['DGNum_of_Units'] = "456/DGOODS/0";
          $this->karr ['DGPack_Type'] = "461/DGOODS/0";
          $this->karr ['DGNet_Qty'] = "466/DGOODS/0";
          $this->karr ['DGUOM'] = "471/DGOODS/0";
          $this->karr ['DGPkg_Inst'] = "476/DGOODS/0";
          $this->karr ['DGAuth'] = "483/DGOODS/0";
          $this->karr ['DGEmer_Ph'] = "484/DGOODS/0";
          $this->karr ['DGAdd_Hand'] = "486/DGOODS/0";
          $this->karr ['DGPack_Grp'] = "489/DGOODS/0";
          $this->karr ['DGReg_Ind'] = "1900/DGOODS/0";
          $this->karr ['DGRep_Qty'] = "1901/DGOODS/0";
          $this->karr ['DGProp_Name'] = "1903/DGOODS/0";
          $this->karr ['DGAFlag'] = "1904/DGOODS/0";
          $this->karr ['DGRadion'] = "1905/DGOODS/0";
          $this->karr ['DGActivity'] = "1906/DGOODS/0";
          $this->karr ['DGActivityM'] = "1907/DGOODS/0";
          $this->karr ['DGRPack_Type'] = "1908/DGOODS/0";
          $this->karr ['DGTran_Index'] = "1909/DGOODS/0";
          $this->karr ['DGLabel_Type'] = "1910/DGOODS/0";
          $this->karr ['DGSurf_Read'] = "1911/DGOODS/0";
          $this->karr ['DGDim_Len'] = "1912/DGOODS/0";
          $this->karr ['DGDim_Wid'] = "1913/DGOODS/0";
          $this->karr ['DGDIM_Hgt'] = "1914/DGOODS/0";
          $this->karr ['DGDim_Units'] = "1915/DGOODS/0";
          $this->karr ['DGPhy_Form'] = "1916/DGOODS/0";
          $this->karr ['DGChem_Form'] = "1917/DGOODS/0";
          $this->karr ['DGPercent'] = "1919/DGOODS/0";
          $this->karr ['DGResp_Party'] = "1920/DGOODS/0";
          $this->karr ['DGInfect_Ph'] = "1921/DGOODS/0";
          $this->karr ['DGCons_Pkg'] = "1923/DGOODS/0";
          $this->karr ['DGOverpack'] = "1924/DGOODS/0";
          $this->karr ['DGOverpack_No'] = "1925/DGOODS/0";
          $this->karr ['DGOverpack_Cons'] = "1926/DGOODS/0";
          $this->karr ['DGOuter_Type'] = "1927/DGOODS/0";
          $this->karr ['DGCons_Flag'] = "1928/DGOODS/0";
          $this->karr ['DGCons_Qty'] = "1929/DGOODS/0";
          $this->karr ['DGA81_Flag'] = "1931/DGOODS/0";
          $this->karr ['DGUndef'] = "3253/DGOODS/0";
         */

        // ********************************************** //
        $this->karr ['TLabel'] = "1282/MANUAL/0";
        $this->karr ['LabelSpecification.ImageType'] = "187/PRINTER/0";
        $this->karr ['Plabel'] = "537/MANUAL/0";
        // ********************************************** //

        $this->karr ['EOT'] = 99;
    }

    public function validate_shipment($shipment) {

        /*
         * ****************************************
         * Carrier/ Service Specific validation
         * ****************************************
         */

        $errors = '';

        /*
         * ****************************************
         * Service Specific rules
         * ****************************************
         */
        switch ($shipment['Service']) {
            case 'IP':
                // Replace existing rule
                $rules['Packages.Package.*.Weight'] = 'required_if:Service,UK48|numeric|max:49.50';
                break;

            case 'UK48':
                // Replace existing rule
                $rules['Packages.Package.*.Weight'] = 'required_if:Service,UK48|numeric|max:49.50';
                break;

            default:
                break;
        }

        $shipment_validation = Validator::make($shipment, $rules);

        if ($shipment_validation->fails()) {

            // Returns Errors as an array
            $errors = $this->build_validation_errors($shipment_validation->errors());
        }

        return $errors;
    }

    function buildMsg($data) {

        /*
         * Set the meter number
         */
        if (isset($this->Accounts['meterNumbers'][$data['PayorFreight']])) {
            $data['meterNumber'] = $this->Accounts['meterNumbers'][$data['PayorFreight']];
        }

        // Identify which Groups we wish to output
        $msgGroups = '';
        $msgGroups[] = 'GENERAL';  // Always output General
        $msgGroups[] = 'SHIPMENT'; // Always output SHIPMENT
        $msgGroups[] = 'PACKAGE'; // Always output SHIPMENT
        $msgGroups[] = 'PAYMENT';  // Always output PAYMENT
        $msgGroups[] = 'ALERT';    // Always output ALERT
        $msgGroups[] = 'OPTION';   // Always output OPTION
        $msgGroups[] = 'PRINTER'; // Output Package level details

        if ($this->getData($data, 'PackagingType') == '01') {
            $msgGroups[] = 'ADM';  // Customers own Packaging
        }

        if ($this->getData($data, 'BrokerSelect') == 'Y') {
            $msgGroups[] = 'BROKER'; // Broker Select Option enabled
        }

        if ($this->getData($data, 'Documents.DocFlag') == 'Y') {
            $msgGroups[] = 'DOCUMENT'; // Document Shipment
        } else {
            $msgGroups[] = 'COMMODITY'; // Commodity details
        }

        if ($this->getData($data, 'DGFlag') != 'N') {
            $msgGroups[] = 'DGOODS'; // Dangerous Goods Flag Set
        }

        if ($this->getData($data, 'DryIce.DryIceFlag') != 'N') {
            $msgGroups[] = 'DRYICE'; // DryIce Flag Set
        }

        if ($this->getData($data, 'Alcohol.AlcoholFlag') != 'N') {
            $msgGroups[] = 'ALCOHOL'; // Alcohol Flag Set
        }

        if ($this->getData($data, 'pkgHeight') > 0) {
            $msgGroups[] = 'PACKAGE'; // Output Package level details
        }

        $msgData = $this->buildGroup($data, $msgGroups);

        // Encode the result
        $msg = '0,"020"' . $this->encode($msgData) . '99,""';

        return $msg;
    }

    function buildGROUP($data, $msgGroups) {

        // Replace field names with correct "020 field numbers"
        foreach ($msgGroups as $requestedGroup) {

            // Loop through all the variables the user may submit
            foreach ($this->karr as $key => $value) {

                // Decode the Field containing Fieldno/Group/Mult
                $tmp = explode('/', $value);

                if (isset($tmp[0])) {
                    $fldno = $tmp[0];
                } else {
                    $fldno = '';
                }

                if (isset($tmp[1])) {
                    $group = $tmp[1];
                } else {
                    $group = '';
                }

                if (isset($tmp[2])) {
                    $mult = $tmp[2];
                } else {
                    $mult = 0;
                }


                // Extract only the fields within the current "Requested Group"
                if ($group == $requestedGroup) {

                    /*
                     * *********************************
                     * Process Fields
                     * *********************************
                     */
                    $value = $this->getData($data, $key);

                    switch ($key) {

                        case "SpecialServices":
                            // $msgData['0'] = $this->special_services($data, $key, $msgData);
                            break;

                        case "PayorFreight":
                            if ($value > "") {
                                $msgData ['23'] = $this->_paymentType[$value];
                                $msgData['20'] = $this->get_payor_account($data, $value);
                                $msgData['1195'] = $this->get_payor_country($data, $value);
                            }
                            break;

                        case "PayorDuty":
                            if ($value > "") {
                                $msgData ['70'] = $this->_paymentType[$value];
                                $msgData['71'] = $this->get_payor_account($data, $value);
                                $msgData['1032'] = $this->get_payor_country($data, $value);
                            }
                            break;

                        case "CustomsClearanceDetail.TermsOfSale":
                            if ($value > "") {
                                $msgData [$fldno] = $this->_terms[$value];
                            }
                            break;

                        case "WeightUnits":
                            if ($value > "") {
                                $msgData [$fldno] = $this->_WeightUnits[$value];
                            }
                            break;

                        case "DimensionUnits":
                            if ($value > "") {
                                $msgData [$fldno] = $this->_SDimensionUnits[$value];
                            }
                            break;

                        case "PackagingType":
                            if ($value > "") {
                                $msgData [$fldno] = $this->_PackageTypes[$value];
                            }
                            break;

                        case "Service":
                            if ($value > "") {
                                $msgData [$fldno] = $this->_svc[$value];
                            }
                            break;

                        case "LabelSpecification.ImageType":
                            $msgData ['187'] = 'PNG';
                            $msgData ['537'] = "C:\\Fedex\\Labels\\";
                            $msgData ['1282'] = 'S';
                            $msgData ['1660'] = 'Y';
                            break;

                        case "CurrencyCode":

                            if ($value > "") {
                                switch ($value) {
                                    case 'GBP':
                                        $msgData [$fldno] = 'UKL';
                                        break;

                                    default:
                                        $msgData [$fldno] = $this->_svc[$value];
                                        break;
                                }
                            }

                            break;

                        case 'ShipDate' :
                            // Change format
                            if ($value > "") {
                                $msgData [$fldno] = date('Ymd', strtotime($value));
                            }
                            break;

                        default:
                            /*
                             * *********************************************************
                             *  Does field require special processing for the multiplier
                             * ********************************************************* 
                             */
                            if ($value > "") {
                                switch ($mult) {
                                    case '0':
                                        // Ignore Multiplier
                                        $msgData [$fldno] = $value;
                                        break;

                                    default:
                                        // Multiply
                                        $val = $this->getData($data, $key);
                                        if ($val != '' && $val > 0) {
                                            $msgData [$key] = $value * $mult;
                                        }
                                        break;
                                }
                            }
                            break;
                    }
                }
            }
        }

        return $msgData;
    }

    public function special_services($data, $key, $msgData) {

        /*
         * SPECIAL OPTIONS Field found
         */
        $options = $this->getData($data, $key);
        foreach ($options as $option) {

            switch ($option) {
                case 'HOLD':
                    $msgData [1200] = 'Y';
                    break;

                case 'DROPOFF':
                    $msgData [1333] = 'Y';
                    break;

                case 'BOOK':
                    $msgData [1272] = 'Y';
                    break;

                case 'SATDELIV':
                    $msgData [1266] = 'Y';
                    break;

                case 'CARGO':
                    $msgData [488] = 'Y';
                    break;

                default:
                    break;
            }
        }
        return $msgData;
    }

    function preProcess($key, $value, $mode) {
        #######################################################################
        //         Fields Requiring PreProcessing of Data
        #######################################################################
        $mult = 0;

        switch ($key) {
            case '69':
            case '119':
            case '1670':
            case '1906':
            case '1909':
                $mult = 100;
                $dec = 2;
                break;

            case '1086':
            case '112':
            case '466':
            case '43':
                $mult = 10;
                $dec = 1;
                break;

            case '1030':
                $mult = 1000000;
                $dec = 6;
                break;

            default:
                break;
        }

        // If Field requires preprocessing
        if ($mult > 0) {
            switch ($mode) {
                case 'encode':
                    $value = round($value * $mult, 0);
                    break;

                case 'decode':
                    $value = round($value / $mult, 0);
                    break;

                default:
                    break;
            }
        }
        return $value;
    }

    function send_message($msg) {

        $seconds = 5;
        $milliseconds = 0;
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $seconds, 'usec' => $milliseconds));
        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $seconds, 'usec' => $milliseconds));
        $connected = socket_connect($socket, $this->_connection['url'], $this->_connection['port']);
        if ($connected) {
            socket_write($socket, $msg, strlen($msg));

            $msgType = substr($msg, 3, 3);
            switch ($msgType) {
                case '020':
                    $this->logMsg('', $msg, 'MSG');
                    break;

                case '120':
                    $this->logMsg('', $msg, 'REPLY');
                    break;

                case '023':
                    $this->logMsg('', $msg, 'CANX');
                    break;

                default:
                    $this->logMsg('', $msg, $msgType);
                    break;
            }

            $string = '';
            $i = 0;
            while (!preg_match('/"99,""/', $string)) {
                $char = socket_read($socket, 1);
                $string .= $char;
            }

            socket_close($socket);
        } else {
            $string = '0,"120"3,"Unable to connect to Carrier System"99,""';
            $to = 'it@antrim.ifsgroup.com,itsupport@antrim.ifsgroup.com';
            $to = 'gmcb@antrim.ifsgroup.com';
            $subject = 'Courier Intl FXRS Server Error';
            $message = 'Web Client unable to communicate with the FXRS Server';
            $headers = 'From: noreply@antrim.ifsgroup.com' . "\r\n" .
                    'Reply-To: it@antrim.ifsgroup.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

            mail($to, $subject, $message, $headers);
        }

        $this->reply = $string;
        return ($string);
    }

    function decode($temp, $log = '') {

        if ($log == TRUE) { //Log the reply to database
            $this->logMsg('', $temp, 'REPLY');
        }

        $reply = '';
        $mvArr = '';
        $not_finished = true;
        $off_set = 0;

        while ($not_finished) {
            if (ord(substr($temp, 0, 1)) == 0) {
                $temp = substr($temp, 1, 999);                       // Remove starting null string if it exists
            }
            if (strlen($temp) > 3) {
                $t = strpos($temp, ',', $off_set);                   // Find position of comma
                $q1 = strpos($temp, '"', $off_set + 1);              // Find Position of first Quote
                $q2 = strpos($temp, '"', $q1 + 1);                   // Find Position of Second Quote
                $FieldNo = substr($temp, $off_set, $t - $off_set);   // Extract Field No
                $mvp = strpos($FieldNo, '-');                        // Check to see if a multivalue field
                if ($mvp > 0) {
                    $FieldNoAbs = substr($FieldNo, 0, $mvp);         // Field No minus any mv additions eg 79-1 becomes 79
                    $ref = substr($FieldNo, $mvp + 1, strlen($FieldNo) - $mvp);
                } else {
                    $FieldNoAbs = $FieldNo;                          // If not mv then just use FieldNo
                    $ref = 1;                                        // Set default ref to 1 (only applies to mv items)
                }
                $FData = substr($temp, $q1 + 1, $q2 - $q1 - 1);      // Extract Data portion
                #######################################################################
                //         PreProcess Data
                $FData = $this->preProcess($FieldNoAbs, $FData, 'decode');

                switch ($FieldNoAbs) {
                    case '29' :
                    case '65' :
                    case '664' :
                    case '3063' :
                    case '3064' :
                        $ref = 2;
                        break;

                    case '526' :
                        $ref = 1;
                        break;
                }
                //         PreProcessing Complete
                #######################################################################

                /*
                 * Code to handle single/ multivalue values
                 */
                switch ($ref) {
                    case '1':
                        // Single Value
                        $reply[$FieldNoAbs] = $FData;
                        break;

                    case '2':
                        // Not Single value so change to array
                        $mvArr = ''; //clear

                        /*
                         * Values may already exist so add
                         * any existing values into the mvArr
                         */
                        if (isset($reply[$FieldNoAbs]) && is_array($reply[$FieldNoAbs])) {
                            foreach ($reply[$FieldNoAbs] as $v) {
                                $mvArr[] = $v;
                            }
                        }

                        // Now add any new values
                        $mvArr[] = $FData; // Store this value
                        // Replace previous values
                        $reply[$FieldNoAbs] = $mvArr;

                        break;

                    default:
                        //$reply[$FieldNoAbs][] = $FData;
                        break;
                }
                /*
                 * Finish Single/ Multivalues
                 */
            } else {
                $reply = '';                                         // Invalid response - so return empty string.
                $not_finished = true;
            }
            if ($FieldNo == "99") {
                $not_finished = false;
            }
            $off_set = $q2 + 1;
        }
        return $reply;
    }

    function encode($arrData) {
        // Recieves an array of field numbers and values
        // Note some of the values are arrays
        $finished = false;
        $msg = '';
        foreach ($arrData as $arrKey => $arrValue) {

            $first_val = '';

            if (is_array($arrValue)) {
                if (isset($arrValue[1])) {
                    $first_val = $arrValue[1];
                }
            } else {
                $first_val = $arrValue;
            }

            if ($first_val <> '' && $first_val <> '!') {

                if (is_array($arrValue)) { // Is this an array?
                    $i = 1;
                    foreach ($arrValue as $value) {
                        if ($value <> '') {
                            $value = $this->preProcess($arrKey, $value, 'encode');
                            $msg = $msg . $arrKey . '-' . $i . ',"' . $value . '"';
                        }
                        $i++;
                    }
                } else {
                    $value = $this->preProcess($arrKey, $arrValue, 'encode');
                    $msg = $msg . $arrKey . ',"' . $value . '"';
                }
            }
        }

        return $msg;
    }

    private function extract_errors($reply) {

        return $this->generate_error($errors, 'Carrier');
    }

    /*
     * *********************************************
     * *********************************************
     * Start of Interface Calls
     * *********************************************
     * *********************************************
     */

    public function checkAddress($address) {

        return $this->generate_success();
    }

    public function requestPickup($pickup_request) {

        return $this->generate_success();
    }

    private function create_pickup_response($reply) {

        return $this->generate_success();
    }

    public function cancelPickup($cancel_request) {

        return $this->generate_success();
    }

    private function cancel_pickup_response($reply) {

        return $this->generate_success();
    }

    public function checkAvailServices($shipment) {

        return $this->generate_success();
    }

    private function create_availability_response($data) {

        return $this->generate_success();
    }

    public function createShipment($shipment) {

        $response = '';
        $errors = $this->validate_shipment($shipment);

        if ($errors == '') {
            $this->transactionHeader = $this->getData($shipment, 'TransactionID');

            $replyMsg = $this->send_message($this->buildMsg($shipment));

            $reply = $this->decode($replyMsg);

            if (isset($reply['3']) && $reply['3'] > '') {

                // Request unsuccessful - return errors
                $response = $this->generate_errors($response, $reply[3]);
            } else {

                // Request succesful - Prepare Response
                $response = $this->create_shipment_response(
                        $reply, $shipment['LabelSpecification']['ImageType'], $shipment['LabelSpecification']['LabelStockType']);
            }
        } else {
            $response = $this->generate_errors($response, $errors);
        }

        return $response;
    }

    private function create_shipment_response($reply, $ImageType, $LabelStockType) {

        $response = $this->generate_success();

        // Add additional data to be returned
        $volWeight = 0;

        $response['AirwayBill'] = $reply['1123'];

        if (is_array($reply['664'])) {

            // Multiple Pieces
            $cnt = count($reply['664']);
            $response['PackageCount'] = $cnt;
            for ($i = 0; $i < $cnt; $i++) {
                $response['Packages']['Package'][$i]['SequenceNumber'] = $i + 1;
                $response['Packages']['Package'][$i]['PackageNo'] = $reply['29'][$i];
                $response['Packages']['Package'][$i]['Barcode'] = $reply['664'][$i];
            }
        } else {

            // Single Piece
            $response['PackageCount'] = 1;
            $response['Packages']['Package'][0]['SequenceNumber'] = 1;
            $response['Packages']['Package'][0]['PackageNo'] = $reply['29'];
            $response['Packages']['Package'][0]['Barcode'] = $reply['664'];
        }

        $response['LabelFormat'] = 'PDF';
        $response['LabelBase64'] = base64_encode($this->generate_pdf($reply['29'], $ImageType, $LabelStockType));

        return $response;
    }

    private function generate_pdf($awbs, $ImageType, $LabelStockType) {

        $label_loc = "http://192.168.10.34/";

        // Parameters (Orientation, Size_Units, Page_Size, Custom Page) 
        // $pdf = new TCPDF($this->_label_defn[$LabelStockType]);
        $pdf = new TCPDF('p', 'in', array(6, 4), true, 'UTF-8', false);

        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        foreach ($awbs as $awb) {
            $pdf->AddPage();
            // $pdf->Image($label_loc . $awb . '.PNG', $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false, $alt = false, $altimgs = array());
            $pdf->Image($label_loc . $awb . '.PNG');
        }

        // Return Raw data (no response headers etc.
        return $pdf->output($awbs[0], 'S');
    }

}

?>
