<?php

namespace App\Http\Controllers\Backend\QrCode;

use App\Http\Controllers\Controller;
use QrCode;

class QrCodeController extends Controller
{
    public function downloadQrCode($dataQr, $name)
    {
        try {
            // Validate input parameters
            if (empty($dataQr) || empty($name)) {
                return response()->json(['error' => 'Invalid input parameters'], 400);
            }

            // Check if $name is a valid filename
            if (!preg_match('/^[\pL\pN_\-\s]+$/u', $name)) {
                return response()->json(['error' => 'Invalid filename'], 400);
            }

            // Generate and stream the QR code as an SVG file
            return response()->streamDownload(
                function () use ($dataQr) {
                    // Generate the QR code with a size of 300 pixels and in SVG format
                    echo QrCode::size(300)
                        ->format('svg')
                        ->generate($dataQr);
                },
                $name . '.svg', // Set the filename of the downloaded SVG
                [
                    'Content-Type' => 'image/svg+xml', // Set the content type header for SVG
                ]
            );
        } catch (\Exception $e) {
            // Handle the exception appropriately, e.g., log the error, return an error response, etc.
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}