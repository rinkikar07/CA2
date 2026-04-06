<?php
/**
 * HIM - AI Helper
 * OpenRouter API integration + fallback responses
 */

require_once __DIR__ . '/config.php';

/**
 * Get AI chat response via OpenRouter API
 */
function getAIResponse($userMessage, $mood, $cyclePhase, $userName = 'dear') {
    $apiKey = OPENROUTER_API_KEY;
    $model = OPENROUTER_MODEL;
    
    // If no API key, use fallback
    if (empty($apiKey) || $apiKey === 'your_openrouter_api_key_here') {
        return getFallbackResponse($userMessage, $mood, $cyclePhase, $userName);
    }
    
    $systemPrompt = buildSystemPrompt($mood, $cyclePhase, $userName);
    
    $data = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage]
        ],
        'max_tokens' => 300,
        'temperature' => 0.8
    ];
    
    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: ' . APP_URL,
            'X-Title: HIM - Her Intelligent Mate'
        ],
        CURLOPT_TIMEOUT => 15
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
    }
    
    // Fallback on API failure
    return getFallbackResponse($userMessage, $mood, $cyclePhase, $userName);
}

/**
 * Build system prompt for AI
 */
function buildSystemPrompt($mood, $cyclePhase, $userName) {
    $phaseContext = [
        'menstrual' => 'She is in her menstrual phase (period). She may be experiencing cramps, fatigue, and low energy. Be extra gentle, comforting, and nurturing.',
        'follicular' => 'She is in her follicular phase. Energy is rising, creativity is high. Be encouraging, motivating, and enthusiastic.',
        'ovulation' => 'She is in her ovulation phase. Peak energy and confidence. Be supportive, celebratory, and empowering.',
        'luteal' => 'She is in her luteal phase (PMS window). She may feel irritable, emotional, or anxious. Be patient, understanding, and soothing.'
    ];
    
    $moodContext = [
        'happy' => 'She is feeling happy. Match her positive energy while being warm.',
        'sad' => 'She is feeling sad. Be extra compassionate, validating, and gentle.',
        'anxious' => 'She is feeling anxious. Be calming, reassuring, and grounding.',
        'angry' => 'She is feeling angry. Acknowledge her feelings without dismissing them. Be validating.',
        'tired' => 'She is feeling tired. Keep responses short and soothing. Suggest rest.',
        'neutral' => 'She is feeling neutral. Be warm and check in gently.',
        'calm' => 'She is feeling calm. Match her peaceful energy.',
        'irritated' => 'She is feeling irritated. Be patient, don\'t be overly cheerful. Validate her feelings.'
    ];
    
    $phase = $phaseContext[$cyclePhase] ?? $phaseContext['menstrual'];
    $moodInfo = $moodContext[$mood] ?? $moodContext['neutral'];
    
    return "You are HIM (Her Intelligent Mate), a warm, empathetic AI companion for women during their menstrual cycle. 
You are talking to {$userName}. 

CONTEXT:
- {$phase}
- {$moodInfo}

RULES:
1. Be warm, empathetic, and non-judgmental. Never be clinical or cold.
2. Use her name occasionally to feel personal.
3. Keep responses concise (2-4 sentences max).
4. Offer practical suggestions when appropriate (breathing, hydration, rest).
5. Never diagnose medical conditions. If health concerns arise, gently suggest consulting a doctor.
6. Use gentle emoji sparingly (💕, 🌸, ☀️, 🌙).
7. Adapt your tone to match her mood and cycle phase.
8. If she seems in pain, acknowledge it first before offering help.
9. You are a supportive friend, not a doctor or therapist.";
}

/**
 * Fallback responses when API is unavailable
 */
function getFallbackResponse($message, $mood, $phase, $name) {
    $messageLower = strtolower($message);
    
    // Keyword-based responses
    if (strpos($messageLower, 'cramp') !== false || strpos($messageLower, 'pain') !== false) {
        $responses = [
            "I'm so sorry you're in pain, {$name} 💕 Try placing a warm heating pad on your tummy and sipping some ginger tea. You're being so brave.",
            "Ouch, cramps are the worst 🌙 Have you tried gentle stretching? Child's pose can work wonders. I'm right here with you.",
            "I wish I could take the pain away, {$name}. A warm bath with some lavender might help. Remember, this will pass 💕"
        ];
    } elseif (strpos($messageLower, 'sad') !== false || strpos($messageLower, 'cry') !== false || strpos($messageLower, 'low') !== false) {
        $responses = [
            "It's okay to feel this way, {$name} 💕 Your emotions are valid. Would you like to talk about it, or shall I share something comforting?",
            "I'm here for you 🌸 Feeling low during your cycle is completely normal. You're not alone in this, and you're doing great just by being here.",
            "Let those feelings flow, {$name}. Sometimes a good cry is the best medicine. Wrap yourself in a cozy blanket — you deserve comfort right now 💕"
        ];
    } elseif (strpos($messageLower, 'tired') !== false || strpos($messageLower, 'exhaust') !== false || strpos($messageLower, 'fatigue') !== false) {
        $responses = [
            "Rest, {$name} 🌙 Your body is working hard right now. It's okay to cancel plans and just be. You've earned this rest.",
            "Fatigue during your cycle is your body asking for care. Take a nap, hydrate, and be kind to yourself 💕",
            "You don't have to be productive today, {$name}. Sometimes the bravest thing is to simply rest 🌸"
        ];
    } elseif (strpos($messageLower, 'anxious') !== false || strpos($messageLower, 'worry') !== false || strpos($messageLower, 'stress') !== false) {
        $responses = [
            "Take a deep breath with me, {$name} 🌸 Breathe in for 4... hold for 4... out for 6. Anxiety during your cycle is normal, and it will ease.",
            "I understand that anxious feeling. Try grounding: name 5 things you can see right now. I'm right here with you 💕",
            "Your worries are valid, {$name}, but they feel bigger right now because of hormonal changes. Be gentle with yourself 🌙"
        ];
    } elseif ($mood === 'happy' || strpos($messageLower, 'happy') !== false || strpos($messageLower, 'good') !== false) {
        $responses = [
            "That makes me so happy to hear, {$name}! ☀️ You deserve all the good vibes. What's making you smile today?",
            "Yay! Your positive energy is beautiful! 🌸 Enjoy this feeling — you've earned it!",
            "I love seeing you happy, {$name}! ☀️ This is the perfect time to do something you love."
        ];
    } else {
        // Phase-based general responses
        $phaseResponses = [
            'menstrual' => [
                "I'm here for you, {$name} 🌙 How are you feeling today? Remember, it's okay to take things slow during your period.",
                "Hey {$name} 💕 Your body is doing something amazing right now. What can I help you with today?",
            ],
            'follicular' => [
                "Hi {$name}! ☀️ You're in your follicular phase — energy is rising! What would you like to talk about?",
                "Great time to try something new, {$name}! 🌱 Your creativity is at its peak. I'm here to chat!",
            ],
            'ovulation' => [
                "Hey {$name}! ☀️ You're glowing! This is your peak — how can I support you today?",
                "Hi there, superstar! 🌟 Ovulation energy is real. What's on your mind?",
            ],
            'luteal' => [
                "Hi {$name} 🌸 How are you holding up? The luteal phase can be tricky. I'm here for whatever you need.",
                "Hey {$name} 💕 Be extra kind to yourself right now. What's on your mind today?",
            ]
        ];
        $responses = $phaseResponses[$phase] ?? $phaseResponses['menstrual'];
    }
    
    return $responses[array_rand($responses)];
}

/**
 * Detect sentiment from message (simple keyword-based)
 */
function detectSentiment($message) {
    $messageLower = strtolower($message);
    
    $positive = ['happy', 'great', 'good', 'love', 'wonderful', 'amazing', 'better', 'excited', 'joy', 'thankful', 'grateful', 'smile', 'laugh'];
    $negative = ['sad', 'bad', 'pain', 'hurt', 'cry', 'angry', 'anxious', 'worried', 'scared', 'lonely', 'depressed', 'terrible', 'awful', 'hate'];
    $neutral = ['okay', 'fine', 'alright', 'normal', 'meh'];
    
    foreach ($positive as $word) {
        if (strpos($messageLower, $word) !== false) return 'positive';
    }
    foreach ($negative as $word) {
        if (strpos($messageLower, $word) !== false) return 'negative';
    }
    foreach ($neutral as $word) {
        if (strpos($messageLower, $word) !== false) return 'neutral';
    }
    
    return 'neutral';
}
