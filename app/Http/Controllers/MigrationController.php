<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Tag;
use App\Models\Ministry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use League\Csv\Reader;

class MigrationController extends Controller
{
    // Planning Center column mappings to Ministrify fields
    protected array $defaultMappings = [
        'first_name' => ['First Name', 'Given Name', 'Nickname'],
        'last_name' => ['Last Name'],
        'email' => ['Home Email', 'Work Email', 'Other Email'],
        'phone' => ['Mobile', 'Home phone', 'Work phone'],
        'birth_date' => ['Birthdate'],
        'address' => ['Home Address Street Line 1'],
        'city' => ['Home Address City'],
        'notes' => ['Membership'],
        'gender' => ['Gender'],
        'marital_status' => ['Status'],
    ];

    public function planningCenter()
    {
        $church = $this->getCurrentChurch();
        $existingCount = Person::where('church_id', $church->id)->count();

        return view('migrations.planning-center', [
            'existingCount' => $existingCount,
            'defaultMappings' => $this->defaultMappings,
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        // Detect delimiter (tab for Planning Center, comma for others)
        $firstLine = strtok($content, "\n");
        $delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ",")) ? "\t" : ",";

        $csv = Reader::createFromString($content);
        $csv->setDelimiter($delimiter);
        $csv->setHeaderOffset(0);

        $headers = $csv->getHeader();
        $records = iterator_to_array($csv->getRecords());

        // Get first 10 records for preview
        $preview = array_slice($records, 0, 10);
        $totalRows = count($records);

        // Auto-detect mappings
        $autoMappings = $this->detectMappings($headers);

        return response()->json([
            'success' => true,
            'headers' => $headers,
            'preview' => array_values($preview),
            'totalRows' => $totalRows,
            'autoMappings' => $autoMappings,
        ]);
    }

    protected function detectMappings(array $headers): array
    {
        $mappings = [];
        $churchHubFields = [
            'first_name' => ['first name', 'given name', 'nickname', "ім'я", 'імя', 'имя'],
            'last_name' => ['last name', 'surname', 'прізвище', 'фамилия'],
            'email' => ['email', 'home email', 'work email', 'пошта', 'почта'],
            'phone' => ['mobile', 'phone', 'home phone', 'cell', 'телефон', 'мобільний'],
            'birth_date' => ['birthdate', 'birthday', 'birth date', 'дата народження', 'день народження'],
            'address' => ['address', 'street', 'home address street line 1', 'адреса'],
            'city' => ['city', 'home address city', 'місто'],
            'gender' => ['gender', 'стать', 'пол'],
            'notes' => ['notes', 'membership', 'нотатки', 'примітки'],
        ];

        foreach ($headers as $header) {
            $headerLower = strtolower(trim($header));
            foreach ($churchHubFields as $field => $patterns) {
                foreach ($patterns as $pattern) {
                    if (str_contains($headerLower, $pattern)) {
                        if (!isset($mappings[$field])) {
                            $mappings[$field] = $header;
                        }
                        break 2;
                    }
                }
            }
        }

        return $mappings;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'mappings' => 'required|array',
            'clear_existing' => 'boolean',
        ]);

        $church = $this->getCurrentChurch();
        $mappings = $request->input('mappings');
        $clearExisting = $request->boolean('clear_existing');

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        // Detect delimiter
        $firstLine = strtok($content, "\n");
        $delimiter = (substr_count($firstLine, "\t") > substr_count($firstLine, ",")) ? "\t" : ",";

        $csv = Reader::createFromString($content);
        $csv->setDelimiter($delimiter);
        $csv->setHeaderOffset(0);

        $records = iterator_to_array($csv->getRecords());

        DB::beginTransaction();
        try {
            // Clear existing data if requested
            if ($clearExisting) {
                Person::where('church_id', $church->id)->delete();
            }

            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($records as $index => $row) {
                try {
                    $firstName = $this->getValue($row, $mappings, 'first_name');
                    $lastName = $this->getValue($row, $mappings, 'last_name');

                    // Skip if no name
                    if (empty($firstName) && empty($lastName)) {
                        $skipped++;
                        continue;
                    }

                    // Build full address
                    $addressParts = [];
                    if ($street = $this->getValue($row, $mappings, 'address')) {
                        $addressParts[] = $street;
                    }
                    if ($city = $this->getValue($row, $mappings, 'city')) {
                        $addressParts[] = $city;
                    }
                    $address = implode(', ', array_filter($addressParts));

                    // Parse birth date
                    $birthDate = $this->parseDate($this->getValue($row, $mappings, 'birth_date'));

                    // Parse gender
                    $gender = $this->parseGender($this->getValue($row, $mappings, 'gender'));

                    // Parse marital status
                    $maritalStatus = $this->parseMaritalStatus($this->getValue($row, $mappings, 'marital_status'));

                    Person::updateOrCreate(
                        [
                            'church_id' => $church->id,
                            'first_name' => $firstName ?: '',
                            'last_name' => $lastName ?: '',
                        ],
                        [
                            'email' => $this->getValue($row, $mappings, 'email'),
                            'phone' => $this->formatPhone($this->getValue($row, $mappings, 'phone')),
                            'address' => $address ?: null,
                            'birth_date' => $birthDate,
                            'gender' => $gender,
                            'marital_status' => $maritalStatus,
                            'notes' => $this->getValue($row, $mappings, 'notes'),
                        ]
                    );
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Рядок " . ($index + 2) . ": " . $e->getMessage();
                    if (count($errors) > 10) {
                        $errors[] = "... та інші помилки";
                        break;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function getValue(array $row, array $mappings, string $field): ?string
    {
        if (!isset($mappings[$field]) || empty($mappings[$field])) {
            return null;
        }

        $column = $mappings[$field];
        $value = $row[$column] ?? null;

        return $value ? trim($value) : null;
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Planning Center format: YYYY-MM-DD
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value);
            }
            // Ukrainian format: DD.MM.YYYY
            if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $value)) {
                return Carbon::createFromFormat('d.m.Y', $value);
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseGender(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        $male = ['male', 'm', 'чоловік', 'чоловіча', 'ч', 'муж', 'мужской'];
        $female = ['female', 'f', 'жінка', 'жіноча', 'ж', 'жен', 'женский'];

        if (in_array($value, $male)) {
            return 'male';
        }
        if (in_array($value, $female)) {
            return 'female';
        }

        return null;
    }

    protected function parseMaritalStatus(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        $married = ['married', 'одружений', 'одружена', 'заміжня', 'женат', 'замужем'];
        $single = ['single', 'неодружений', 'неодружена', 'холост', 'не замужем'];
        $widowed = ['widowed', 'вдівець', 'вдова'];
        $divorced = ['divorced', 'розлучений', 'розлучена', 'разведен', 'разведена'];

        if (in_array($value, $married)) {
            return 'married';
        }
        if (in_array($value, $single)) {
            return 'single';
        }
        if (in_array($value, $widowed)) {
            return 'widowed';
        }
        if (in_array($value, $divorced)) {
            return 'divorced';
        }

        return null;
    }

    protected function formatPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        return $phone ?: null;
    }
}
