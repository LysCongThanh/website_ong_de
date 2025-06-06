<?php

namespace App\Services\ModelAI;

use Illuminate\Support\Facades\Log;

class ModerationService
{
    /**
     * Kiểm tra và xử lý nội dung dựa trên kết quả kiểm duyệt từ OpenAI
     *
     * @param array $moderationResult Kết quả từ OpenAI Moderation API
     * @param string $content Nội dung gốc đã kiểm tra
     * @return array Trạng thái và thông báo
     */
    public function handleModerationResult(array $moderationResult, string $content): array
    {
        if (isset($moderationResult['results'][0]['flagged']) && $moderationResult['results'][0]['flagged'] === true) {
            $categories = $moderationResult['results'][0]['categories'] ?? [];
            $categoryScores = $moderationResult['results'][0]['category_scores'] ?? [];

            Log::warning('Content flagged by moderation', [
                'content_preview' => mb_substr($content, 0, 50) . (mb_strlen($content) > 50 ? '...' : ''),
                'categories' => $categories,
                'category_scores' => $categoryScores
            ]);

            return [
                'status' => 'rejected',
                'message' => $this->generatePoliteRejection($categories, $categoryScores),
                'categories' => $categories
            ];
        }

        if (isset($moderationResult['error'])) {
            Log::error('Moderation API Error: ' . $moderationResult['error']);
            return [
                'status' => 'error',
                'message' => 'Xin lỗi, đã xảy ra lỗi khi xử lý yêu cầu của bạn. Vui lòng thử lại sau.',
                'error' => $moderationResult['error']
            ];
        }

        return [
            'status' => 'safe',
            'message' => null
        ];
    }

    /**
     * Tạo phản hồi từ chối lịch sự dựa trên danh mục nội dung không phù hợp
     *
     * @param array $categories Các danh mục bị đánh dấu
     * @param array $categoryScores Điểm số các danh mục (để xác định mức độ nghiêm trọng)
     * @return string Thông điệp từ chối
     */
    private function generatePoliteRejection(array $categories, array $categoryScores = []): string
    {
        $responseTemplates = $this->getResponseTemplates();

        $violatedCategories = [];
        $highestScore = 0;
        $mostSeriousCategory = 'default';

        foreach ($categories as $category => $flagged) {
            if ($flagged) {
                $mainCategory = $this->categorizeViolation($category);
                $violatedCategories[$mainCategory] = true;

                $score = $categoryScores[$category] ?? 0;
                if ($score > $highestScore) {
                    $highestScore = $score;
                    $mostSeriousCategory = $mainCategory;
                }
            }
        }

        if (empty($violatedCategories)) {
            $templateKey = 'default';
        }

        elseif (count($violatedCategories) > 1) {
            $templateKey = $mostSeriousCategory;
        }

        else {
            $keys = array_keys($violatedCategories);
            $templateKey = $keys[0];
        }


        $templates = $responseTemplates[$templateKey] ?? $responseTemplates['default'];

        $response = $templates[array_rand($templates)];

        return $response;
    }

    /**
     * Phân loại vi phạm thành các nhóm chính
     *
     * @param string $category Danh mục từ OpenAI
     * @return string Nhóm chính
     */
    private function categorizeViolation(string $category): string
    {
        if (str_contains($category, 'harassment')) {
            return 'harassment';
        }
        if (str_contains($category, 'hate')) {
            return 'hate';
        }
        if (str_contains($category, 'self-harm')) {
            return 'self-harm';
        }
        if (str_contains($category, 'sexual')) {
            return 'sexual';
        }
        if (str_contains($category, 'violence')) {
            return 'violence';
        }
        return 'default';
    }

    /**
     * Danh sách các mẫu phản hồi cho từng loại vi phạm
     *
     * @return array Các mẫu phản hồi
     */
    private function getResponseTemplates(): array
    {
        return [
            'harassment' => [
                "Tôi hiểu cảm xúc có thể dâng trào, nhưng hãy cùng giữ cuộc trò chuyện trong sự tôn trọng nhé.",
                "Tôi muốn giúp bạn, nhưng với tinh thần tích cực hơn. Bạn có câu hỏi nào khác không?",
                "Hãy cùng tạo không gian trao đổi thân thiện. Tôi có thể hỗ trợ gì cho bạn?",
                "Tôi luôn sẵn sàng hỗ trợ trong sự tôn trọng lẫn nhau. Bạn muốn hỏi gì tiếp theo?",
                "Tôi tin rằng chúng ta có thể trò chuyện một cách hòa nhã hơn. Bạn cần giúp gì không?",
                "Tôi mong muốn cuộc đối thoại của chúng ta mang tính xây dựng. Bạn có ý tưởng nào khác không?",
                "Hãy thử đặt câu hỏi theo cách tích cực hơn, tôi sẽ rất vui được hỗ trợ!",
                "Tôi ở đây để giúp bạn, nhưng hãy giữ tinh thần tôn trọng nhé. Bạn muốn hỏi gì?",
                "Tôi muốn duy trì một môi trường trò chuyện tích cực. Bạn có thể thử câu hỏi khác không?",
                "Tôi luôn khuyến khích sự tôn trọng trong trao đổi. Tôi có thể giúp gì thêm cho bạn?",
                "Hãy cùng nhau biến cuộc trò chuyện này thành một trải nghiệm tích cực nhé!",
                "Tôi sẵn lòng hỗ trợ nếu chúng ta giữ được sự tôn trọng. Bạn cần thông tin gì?",
                "Tôi tin rằng một cách tiếp cận nhẹ nhàng sẽ hiệu quả hơn. Bạn có câu hỏi nào không?",
                "Tôi muốn giúp bạn trong một không gian thân thiện. Hãy thử chủ đề khác nhé.",
                "Tôi rất vui được hỗ trợ nếu chúng ta giữ được sự lịch sự. Bạn muốn hỏi gì?",
                "Tôi hiểu bạn có ý kiến mạnh mẽ, nhưng hãy cùng trao đổi một cách ôn hòa hơn nhé.",
                "Tôi luôn hướng đến sự tích cực trong trò chuyện. Bạn có câu hỏi nào khác không?",
                "Tôi muốn đảm bảo cuộc trò chuyện của chúng ta thoải mái cho cả hai. Bạn cần gì?",
                "Hãy thử cách tiếp cận nhẹ nhàng hơn, tôi sẽ cố gắng giúp bạn hết sức!",
                "Tôi ở đây để hỗ trợ bạn một cách tích cực. Bạn muốn thảo luận gì tiếp theo?",
                "Tôi mong muốn giữ không gian này thân thiện. Bạn có câu hỏi nào khác không?",
                "Tôi tin rằng sự tôn trọng sẽ mang lại kết quả tốt hơn. Tôi có thể giúp gì cho bạn?",
                "Hãy cùng trao đổi một cách thoải mái hơn nhé. Bạn cần hỗ trợ gì?",
                "Tôi luôn sẵn sàng giúp đỡ nếu chúng ta giữ được sự hòa nhã. Bạn muốn hỏi gì?",
                "Tôi muốn hỗ trợ bạn trong một không gian tích cực. Hãy thử câu hỏi khác nhé.",
                "Tôi hiểu bạn có thể đang bức xúc, nhưng hãy thử cách tiếp cận nhẹ nhàng hơn nhé.",
                "Tôi rất vui được giúp bạn nếu chúng ta trò chuyện một cách tôn trọng. Bạn cần gì?",
                "Hãy cùng nhau tạo nên một cuộc trao đổi tích cực. Bạn có ý tưởng nào không?",
                "Tôi luôn ưu tiên sự lịch sự trong giao tiếp. Bạn có câu hỏi nào khác không?",
                "Tôi muốn giúp bạn với tinh thần tích cực nhất. Bạn muốn thảo luận gì?",
            ],
            'hate' => [
                "Tôi tin vào sự tôn trọng và hòa nhập. Bạn có thể hỏi tôi về chủ đề khác không?",
                "Tôi muốn giữ cuộc trò chuyện này tích cực và thân thiện. Bạn cần giúp gì?",
                "Tôi ở đây để hỗ trợ trong một không gian hòa nhã. Bạn có câu hỏi nào khác không?",
                "Tôi đánh giá cao sự đa dạng và tôn trọng. Hãy thử một chủ đề khác nhé.",
                "Tôi luôn hướng đến sự tích cực trong trao đổi. Bạn muốn biết gì thêm?",
                "Tôi muốn tạo một môi trường trò chuyện thân thiện. Bạn có ý tưởng nào không?",
                "Tôi tin rằng mọi người đều xứng đáng được tôn trọng. Tôi có thể giúp gì cho bạn?",
                "Hãy cùng nhau xây dựng một cuộc trò chuyện tích cực hơn. Bạn cần gì?",
                "Tôi ở đây để hỗ trợ bạn một cách hòa nhã. Bạn có câu hỏi khác không?",
                "Tôi muốn đảm bảo rằng cuộc trò chuyện này mang tính xây dựng. Hãy thử nhé!",
                "Tôi luôn ưu tiên sự tôn trọng trong giao tiếp. Bạn muốn thảo luận gì?",
                "Tôi tin rằng một không gian hòa nhập sẽ tốt hơn. Bạn có câu hỏi nào không?",
                "Tôi rất vui được giúp bạn với tinh thần tích cực. Bạn cần hỗ trợ gì?",
                "Hãy cùng tạo nên một cuộc trao đổi thân thiện hơn nhé. Bạn muốn hỏi gì?",
                "Tôi ở đây để hỗ trợ bạn trong sự tôn trọng. Bạn có chủ đề nào khác không?",
                "Tôi muốn giữ môi trường này tích cực và an toàn. Bạn cần thông tin gì?",
                "Tôi tin vào giá trị của sự hòa nhã. Bạn có thể thử câu hỏi khác không?",
                "Tôi luôn sẵn sàng giúp bạn với tinh thần xây dựng. Bạn muốn biết gì?",
                "Hãy thử một cách tiếp cận tích cực hơn, tôi sẽ rất vui được hỗ trợ!",
                "Tôi muốn đảm bảo rằng chúng ta có một cuộc trò chuyện thoải mái. Bạn cần gì?",
                "Tôi ở đây để giúp bạn trong sự tôn trọng lẫn nhau. Bạn có ý tưởng nào không?",
                "Tôi tin rằng sự đa dạng làm cuộc sống phong phú hơn. Tôi có thể giúp gì?",
                "Hãy cùng nhau tạo nên một không gian tích cực nhé. Bạn muốn hỏi gì?",
                "Tôi luôn hướng đến sự thân thiện trong giao tiếp. Bạn có câu hỏi nào không?",
                "Tôi muốn hỗ trợ bạn một cách tích cực nhất. Bạn cần thông tin gì?",
                "Tôi ở đây để đảm bảo cuộc trò chuyện mang tính xây dựng. Bạn muốn thảo luận gì?",
                "Tôi tin rằng tôn trọng lẫn nhau là nền tảng tốt nhất. Bạn có câu hỏi khác không?",
                "Hãy thử chủ đề khác để chúng ta cùng trao đổi thoải mái hơn nhé!",
                "Tôi luôn sẵn sàng giúp bạn trong sự hòa nhã. Bạn muốn biết gì thêm?",
                "Tôi muốn giữ không gian này thân thiện và tích cực. Bạn cần hỗ trợ gì?",
            ],
            'self-harm' => [
                "Sức khỏe của bạn rất quan trọng. Hãy liên hệ chuyên gia tư vấn nếu cần nhé.",
                "Tôi quan tâm đến sự an toàn của bạn. Bạn có thể tìm sự hỗ trợ từ chuyên gia tâm lý.",
                "Nếu bạn cần ai đó lắng nghe, hãy thử liên hệ đường dây nóng hỗ trợ tâm lý.",
                "Tôi muốn bạn được an toàn. Có rất nhiều dịch vụ hỗ trợ sức khỏe tinh thần đấy.",
                "Sức khỏe tinh thần là ưu tiên hàng đầu. Bạn có thể nói chuyện với chuyên gia nhé.",
                "Tôi rất lo lắng cho bạn. Hãy tìm sự giúp đỡ từ những người có chuyên môn.",
                "Bạn không cần phải đối mặt một mình. Hãy liên hệ với dịch vụ tư vấn tâm lý.",
                "Tôi khuyến khích bạn tìm sự hỗ trợ từ chuyên gia. Họ sẽ giúp bạn vượt qua.",
                "Sự an toàn của bạn là điều tôi quan tâm. Hãy thử gọi đường dây hỗ trợ nhé.",
                "Tôi tin rằng bạn xứng đáng được giúp đỡ. Hãy liên hệ với chuyên gia tâm lý.",
                "Nếu bạn cần hỗ trợ, có rất nhiều nguồn lực ngoài kia sẵn sàng giúp bạn.",
                "Tôi muốn bạn biết rằng có người sẵn sàng lắng nghe. Hãy tìm chuyên gia nhé.",
                "Sức khỏe tinh thần rất quan trọng. Bạn có thể liên hệ dịch vụ hỗ trợ gần nhất.",
                "Tôi rất mong bạn được an toàn. Hãy tìm đến sự giúp đỡ từ chuyên gia.",
                "Bạn xứng đáng được hỗ trợ. Hãy thử liên hệ với một nhà tư vấn tâm lý nhé.",
                "Tôi lo lắng cho bạn. Có những dịch vụ miễn phí có thể giúp bạn ngay bây giờ.",
                "Hãy ưu tiên sức khỏe của bạn. Chuyên gia tâm lý có thể hỗ trợ bạn tốt hơn.",
                "Tôi muốn bạn biết rằng có người quan tâm. Hãy liên hệ với dịch vụ hỗ trợ.",
                "Nếu bạn đang khó khăn, hãy tìm đến chuyên gia. Họ sẽ giúp bạn vượt qua.",
                "Sự an toàn của bạn là điều tôi mong muốn. Hãy gọi đường dây nóng nếu cần.",
                "Tôi tin bạn có thể vượt qua với sự hỗ trợ đúng đắn. Hãy tìm chuyên gia nhé.",
                "Hãy chăm sóc bản thân. Có rất nhiều người sẵn sàng giúp bạn ngoài kia.",
                "Tôi muốn bạn được hỗ trợ tốt nhất. Hãy liên hệ với dịch vụ tư vấn tâm lý.",
                "Sức khỏe tinh thần cần được chú trọng. Bạn có thể tìm chuyên gia gần nhất.",
                "Tôi rất quan tâm đến bạn. Hãy tìm sự giúp đỡ từ những người có kinh nghiệm.",
                "Bạn không cô đơn đâu. Có các dịch vụ hỗ trợ tâm lý luôn sẵn sàng.",
                "Tôi mong bạn tìm được sự hỗ trợ cần thiết. Hãy liên hệ chuyên gia nhé.",
                "Hãy để ý đến sức khỏe của bạn. Chuyên gia có thể giúp bạn vượt qua khó khăn.",
                "Tôi lo cho sự an toàn của bạn. Hãy tìm đến dịch vụ tư vấn nếu cần.",
                "Tôi muốn bạn biết rằng có hy vọng. Hãy liên hệ với chuyên gia tâm lý nhé.",
            ],
            'sexual' => [
                "Tôi tập trung vào các chủ đề chuyên môn khác. Bạn có câu hỏi nào không?",
                "Tôi không thể hỗ trợ với nội dung này. Hãy thử một chủ đề khác nhé.",
                "Tôi ở đây để giúp với các vấn đề chuyên môn. Bạn cần thông tin gì?",
                "Tôi không trả lời được yêu cầu này. Bạn có câu hỏi khác không?",
                "Tôi chuyên về các chủ đề khác nhau. Bạn muốn thảo luận gì tiếp theo?",
                "Tôi không thể giúp với nội dung này. Tôi sẵn sàng hỗ trợ chủ đề khác.",
                "Hãy thử hỏi về một lĩnh vực khác, tôi sẽ rất vui được hỗ trợ!",
                "Tôi được thiết kế để trả lời các câu hỏi chuyên môn. Bạn cần gì?",
                "Tôi không thể đáp ứng yêu cầu này. Bạn có ý tưởng nào khác không?",
                "Tôi tập trung vào việc cung cấp thông tin hữu ích. Bạn muốn hỏi gì?",
                "Tôi không hỗ trợ nội dung này. Hãy thử một câu hỏi khác nhé.",
                "Tôi ở đây để giúp với nhiều chủ đề khác. Bạn cần hỗ trợ gì?",
                "Tôi không thể trả lời về chủ đề này. Bạn có câu hỏi nào khác không?",
                "Tôi rất vui được hỗ trợ với các lĩnh vực khác. Bạn muốn biết gì?",
                "Tôi không xử lý được yêu cầu này. Hãy thử hỏi về chủ đề khác nhé.",
                "Tôi tập trung vào các câu trả lời chuyên môn. Bạn cần thông tin gì?",
                "Tôi không thể giúp với nội dung này. Bạn có câu hỏi nào khác không?",
                "Hãy thử một chủ đề phù hợp hơn, tôi sẽ cố gắng hỗ trợ bạn!",
                "Tôi ở đây để cung cấp thông tin hữu ích. Bạn muốn thảo luận gì?",
                "Tôi không hỗ trợ lĩnh vực này. Bạn có ý tưởng nào khác không?",
                "Tôi sẵn sàng giúp bạn với các chủ đề khác. Bạn cần gì?",
                "Tôi không thể trả lời yêu cầu này. Hãy thử hỏi điều gì đó khác nhé.",
                "Tôi tập trung vào việc hỗ trợ chuyên môn. Bạn có câu hỏi nào không?",
                "Tôi không xử lý nội dung này. Bạn muốn biết thêm về gì?",
                "Tôi ở đây để giúp với các vấn đề khác. Bạn có câu hỏi nào không?",
                "Hãy thử một chủ đề khác, tôi sẽ rất vui được hỗ trợ bạn!",
                "Tôi không thể hỗ trợ với nội dung này. Bạn cần thông tin gì khác?",
                "Tôi tập trung vào các lĩnh vực phù hợp hơn. Bạn muốn hỏi gì?",
                "Tôi không trả lời được yêu cầu này. Bạn có ý tưởng nào khác không?",
                "Tôi sẵn sàng giúp với các chủ đề khác nhau. Bạn cần hỗ trợ gì?",
            ],
            'violence' => [
                "Tôi muốn giữ không gian này an toàn và tích cực. Bạn có câu hỏi nào khác không?",
                "Tôi không thể hỗ trợ với nội dung này. Hãy thử một chủ đề khác nhé.",
                "Tôi ở đây để tạo ra cuộc trò chuyện tích cực. Bạn cần giúp gì?",
                "Tôi tin vào sự an toàn và hòa bình. Bạn có thể hỏi về chủ đề khác không?",
                "Hãy cùng nhau xây dựng một môi trường thân thiện. Bạn muốn biết gì?",
                "Tôi không trả lời về bạo lực. Tôi sẵn sàng giúp với các chủ đề khác.",
                "Tôi muốn đảm bảo cuộc trò chuyện này mang tính xây dựng. Bạn cần gì?",
                "Tôi luôn ưu tiên sự tích cực trong trao đổi. Bạn có câu hỏi nào không?",
                "Tôi không hỗ trợ nội dung này. Hãy thử hỏi về điều gì đó khác nhé.",
                "Tôi ở đây để cung cấp thông tin an toàn. Bạn muốn thảo luận gì?",
                "Tôi muốn giữ môi trường này hòa nhã. Bạn có ý tưởng nào không?",
                "Tôi không thể giúp với chủ đề này. Bạn cần hỗ trợ gì khác?",
                "Hãy thử một cách tiếp cận tích cực hơn, tôi sẽ rất vui được hỗ trợ!",
                "Tôi tin rằng một không gian an toàn là tốt nhất. Bạn có câu hỏi nào không?",
                "Tôi ở đây để giúp bạn trong sự hòa nhã. Bạn muốn hỏi gì?",
                "Tôi không trả lời về bạo lực. Bạn có chủ đề nào khác không?",
                "Tôi muốn duy trì sự tích cực trong trò chuyện. Bạn cần thông tin gì?",
                "Tôi không hỗ trợ nội dung này. Hãy thử một câu hỏi khác nhé.",
                "Tôi luôn hướng đến sự an toàn và thân thiện. Bạn muốn biết gì?",
                "Hãy cùng tạo nên một cuộc trao đổi tích cực hơn. Bạn có ý tưởng nào không?",
                "Tôi không thể giúp với chủ đề này. Bạn có câu hỏi nào khác không?",
                "Tôi ở đây để hỗ trợ trong một không gian an toàn. Bạn cần gì?",
                "Tôi muốn giữ cuộc trò chuyện này nhẹ nhàng. Bạn muốn thảo luận gì?",
                "Tôi không xử lý nội dung bạo lực. Bạn có thể hỏi về điều gì khác không?",
                "Tôi tin vào sự tích cực trong giao tiếp. Bạn cần hỗ trợ gì?",
                "Hãy thử một chủ đề khác để chúng ta cùng trao đổi thoải mái nhé!",
                "Tôi không thể hỗ trợ với nội dung này. Bạn muốn biết gì thêm?",
                "Tôi ở đây để giúp với tinh thần xây dựng. Bạn có câu hỏi nào không?",
                "Tôi muốn đảm bảo không gian này an toàn. Bạn cần thông tin gì?",
                "Tôi luôn sẵn sàng hỗ trợ trong sự hòa nhã. Bạn muốn hỏi gì?",
            ],
            'default' => [
                "Xin lỗi, tôi không thể hỗ trợ với yêu cầu này. Bạn có câu hỏi nào khác không?",
                "Tôi không xử lý được nội dung này. Hãy thử một chủ đề khác nhé.",
                "Rất tiếc, tôi không thể đáp ứng yêu cầu này. Bạn cần giúp gì?",
                "Yêu cầu này nằm ngoài khả năng của tôi. Bạn muốn hỏi gì tiếp theo?",
                "Tôi không thể cung cấp thông tin về chủ đề này. Bạn có ý tưởng nào không?",
                "Hãy thử một câu hỏi khác, tôi sẽ rất vui được hỗ trợ bạn!",
                "Tôi không trả lời được yêu cầu này. Bạn cần thông tin gì khác?",
                "Tôi được thiết kế để giúp với các chủ đề khác. Bạn muốn biết gì?",
                "Tôi không thể hỗ trợ với nội dung này. Bạn có câu hỏi nào không?",
                "Rất tiếc, tôi không xử lý được yêu cầu này. Bạn cần gì khác?",
                "Tôi ở đây để giúp với nhiều chủ đề khác. Bạn muốn thảo luận gì?",
                "Tôi không thể đáp ứng yêu cầu này. Hãy thử hỏi điều gì đó khác nhé.",
                "Yêu cầu của bạn không nằm trong phạm vi của tôi. Bạn có ý tưởng nào không?",
                "Tôi không hỗ trợ nội dung này. Bạn muốn biết thêm về gì?",
                "Hãy thử một chủ đề khác, tôi sẵn sàng hỗ trợ bạn ngay!",
                "Tôi không thể giúp với yêu cầu này. Bạn cần thông tin gì?",
                "Tôi ở đây để hỗ trợ với các vấn đề khác. Bạn có câu hỏi nào không?",
                "Tôi không trả lời được nội dung này. Bạn muốn hỏi gì tiếp theo?",
                "Rất tiếc, tôi không thể xử lý yêu cầu này. Bạn cần giúp gì?",
                "Tôi được thiết kế cho các chủ đề khác nhau. Bạn muốn biết gì?",
                "Tôi không hỗ trợ với nội dung này. Hãy thử một câu hỏi khác nhé.",
                "Yêu cầu này không phù hợp với tôi. Bạn có ý tưởng nào khác không?",
                "Tôi ở đây để giúp với tinh thần tích cực. Bạn cần gì?",
                "Tôi không thể đáp ứng yêu cầu này. Bạn muốn thảo luận gì?",
                "Hãy thử hỏi về điều gì đó khác, tôi sẽ cố gắng hỗ trợ bạn!",
                "Tôi không xử lý được nội dung này. Bạn có câu hỏi nào khác không?",
                "Tôi sẵn sàng giúp với các chủ đề khác. Bạn cần thông tin gì?",
                "Tôi không thể giúp với yêu cầu này. Bạn muốn biết gì thêm?",
                "Rất tiếc, tôi không hỗ trợ nội dung này. Bạn có ý tưởng nào không?",
                "Tôi ở đây để hỗ trợ bạn với các vấn đề khác. Bạn muốn hỏi gì?",
            ],
        ];
    }
}