<?php
$responseHTML = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prompt'])) {
    $prompt = trim($_POST['prompt']);

    $postData = [
        "messages" => [
            ["role" => "system", "content" => "You are VITA AI, a vigilant and intelligent care assistant. Your primary role is to monitor patient health by analyzing the following data, which will be provided below this prompt:

SpO2 levels (oxygen saturation)

Heart rate

Weight

Meal times (frequency and timing)

Medications (names, dosages, and schedules)

Chronic illnesses or diagnosed medical conditions

Your Responsibilities:
Analyze Health Data Patterns:
Evaluate trends over time to identify potential concerns or improvements in the patient's health status.

Detect Anomalies or Spikes:
Identify any abnormal fluctuations or values that fall outside the expected range. Use these references:

Heart Rate (resting):

Normal: 60–100 bpm

<60 bpm: Investigate if symptoms present (may be normal for athletes)

100 bpm: Possible tachycardia – flag if recurring

SpO2:

Normal: 95–100%

Acceptable (some with chronic lung issues): ≥90%

Caution: <92%

Alert: <88% (risk of hypoxia – seek immediate medical attention)

Generate Formal HTML Reports:
Present all findings, alerts, and recommendations using clean, structured HTML. Apply a professional blue theme for headings, borders, and highlights. The format should be suitable for use in medical dashboards or printable care reports.

Issue Alerts When Needed:
Clearly highlight any critical conditions or metrics that require urgent attention.

Provide Actionable Recommendations:
Suggest changes to the patient's:

Daily routine

Diet or activity

Medication adherence

And advise when to contact a healthcare provider

Output Instructions:
Always output in HTML format, small and informative

Style the response with a blue color theme (e.g., #007BFF for primary elements)

Use formal tone in all messages

Emphasize safety, clarity, and medical relevance

"],
            ["role" => "user", "content" => $prompt]
        ]
    ];

    $ch = curl_init('https://ai.hackclub.com/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $responseHTML = '<p style="color:red;">Request Error: ' . curl_error($ch) . '</p>';
    } else {
        $decoded = json_decode($response, true);
        $rawContent = $decoded['choices'][0]['message']['content'] ?? 'No response received.';

        // Clean AI response
        $cleaned = preg_replace('/<think>.*?<\/think>/s', '', $rawContent);
        $cleaned = preg_replace('/```[a-zA-Z]*\n?/', '', $cleaned);
        $cleaned = str_replace("```", '', $cleaned);

        $responseHTML = $cleaned;
    }

    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>VITA AI – Medical Chat Assistant</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f4f9fc;
      margin: 0;
      padding: 0;
    }
    header {
      background-color: #0088cc;
      color: white;
      padding: 1.5rem 2rem;
      text-align: center;
    }
    main {
      max-width: 900px;
      margin: 2rem auto;
      background: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h1 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    textarea {
      width: 100%;
      height: 160px;
      padding: 1rem;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      resize: vertical;
      box-sizing: border-box;
    }
    button {
      background-color: #0088cc;
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      margin-top: 1rem;
      font-size: 1rem;
      border-radius: 8px;
      cursor: pointer;
    }
    button:hover {
      background-color: #0072aa;
    }
    #response {
      margin-top: 2rem;
      border-top: 1px solid #eee;
      padding-top: 2rem;
    }
    .footer {
      text-align: center;
      font-size: 0.8rem;
      color: #888;
      margin-top: 4rem;
      padding-bottom: 1rem;
    }
  </style>
</head>
<body>

  <header>
    <h1>VITA AI – Medical Assistant</h1>
    <p>Enter patient data or ask a medical question.</p>
  </header>

  <main>
    <form method="post" action="">
      <label for="prompt"><strong>Prompt:</strong></label>
      <textarea id="prompt" name="prompt" required placeholder="E.g., Analyze heart rate and oxygen saturation for John Carter..."><?php echo htmlspecialchars($_POST['prompt'] ?? ''); ?></textarea>
      <br>
      <button type="submit">Analyze with VITA AI</button>
    </form>

    <?php if (!empty($responseHTML)): ?>
    <div id="response">
      <h2>VITA AI Response:</h2>
      <?php echo $responseHTML; ?>
    </div>
    <?php endif; ?>
  </main>

  <div class="footer">© 2025 VITA AI</div>

</body>
</html>
