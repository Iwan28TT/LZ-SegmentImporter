How to Run the Tool:

1. Open the Command Prompt.

2. Navigate to the location of "segment.phar."

3. Begin by setting the API key using the command:

php segment.phar destination:set "api-key"

This initiates an integration test to verify your Segment setup. Double-check by running:

php segment.phar destination:get

If uncertain about the correctness of the API key, confirm it with the above command.

Note: This creates a folder named "segment" with a .apikey file (do not modify it).

4. You can export a CSV file for:

Tracking data: php segment.phar track:export
Group data: php segment.phar group:export
User identification data: php segment.phar identify:export


5. If you have created or exported a CSV file for tracking data, use the following command to import it:
Do not modify the column headers that are given

If you use the csv file from the segment folder, the csv name is enough example: (test.csv) and from your desktop you need the full path: example:
C:\Users\Probo\OneDrive - Probo\Bureaublad\csv\test.csv
php segment.phar track:import

The path has to be between the quotation marks " "

6. For additional assistance, use:

php segment.phar assistent

Good Luck!