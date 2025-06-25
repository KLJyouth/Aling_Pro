<?php declare(strict_types=1];

/**
 * �ļ�����TokenizerInterface.php
 * �����������ִ����ӿ� - �������зִ�������ʵ�ֵķ���
 * ����ʱ�䣺2025-01-XX
 * ����޸ģ�2025-01-XX
 * �汾��1.0.0
 *
 * @package AlingAi\Engines\NLP
 * @author AlingAi Team
 * @license MIT
 */

namespace AlingAi\Engines\NLP;

/**
 * �ִ����ӿ�
 *
 * ���зִ���������ʵ������ӿڣ��ṩ�������ı��ִʹ���
 */
interface TokenizerInterface
{
    /**
     * �ִʷ��������ı��ָ�Ϊ��Ԫ��token��
     *
     * @param string $text Ҫ�ִʵ��ı�
     * @param array $options �ִ�ѡ��
     * @return array �ִʽ�����飬ÿ��Ԫ�ذ�����Ԫ�ı���λ�á����͵���Ϣ
     */
    public function tokenize(string $text, array $options = []): array;
    
    /**
     * ��ȡͣ�ô��б�
     *
     * @param string|null $language ���Դ��룬Ϊnullʱ���ص�ǰ���õ�����
     * @return array ͣ�ô��б�
     */
    public function getStopwords(?string $language = null): array;
    
    /**
     * ����Զ���ͣ�ô�
     *
     * @param array $words Ҫ��ӵ�ͣ�ô�
     * @param string|null $language ���Դ��룬Ϊnullʱʹ�õ�ǰ���õ�����
     * @return bool �Ƿ���ӳɹ�
     */
    public function addStopwords(array $words, ?string $language = null): bool;
    
    /**
     * �Ƴ�ͣ�ô�
     *
     * @param array $words Ҫ�Ƴ���ͣ�ô�
     * @param string|null $language ���Դ��룬Ϊnullʱʹ�õ�ǰ���õ�����
     * @return bool �Ƿ��Ƴ��ɹ�
     */
    public function removeStopwords(array $words, ?string $language = null): bool;
    
    /**
     * ���ִʽ��ת��Ϊ�ַ���
     *
     * @param array $tokens �ִʽ��
     * @param string $delimiter �ָ���
     * @return string ת������ַ���
     */
    public function tokensToString(array $tokens, string $delimiter = ' '): string;
    
    /**
     * ���˷ִʽ��
     *
     * @param array $tokens ԭʼ�ִʽ��
     * @param array $options ����ѡ����Ƴ�ͣ�ôʡ�����
     * @return array ���˺�ķִʽ��
     */
    public function filterTokens(array $tokens, array $options = []): array;
    
    /**
     * ��ȡ�ִ�����Ϣ
     *
     * @return array �ִ�����Ϣ���������ơ��汾��֧�����Ե�
     */
    public function getTokenizerInfo(): array;
    
    /**
     * �������
     *
     * @param string $text Ҫ�����ı�
     * @return string|null ��⵽�����Դ��룬���޷�����򷵻�null
     */
    public function detectLanguage(string $text): ?string;
    
    /**
     * ��ȡ�ʸ�
     * 
     * @param string $word Ҫ��ȡ�ʸɵĵ���
     * @param string|null $language ���Դ��룬Ϊnullʱʹ�õ�ǰ���õ�����
     * @return string ��ȡ�Ĵʸ�
     */
    public function stem(string $word, ?string $language = null): string;
    
    /**
     * ���λ�ԭ
     * 
     * @param string $word Ҫ��ԭ�ĵ���
     * @param string|null $language ���Դ��룬Ϊnullʱʹ�õ�ǰ���õ�����
     * @return string ��ԭ��Ĵ��Σ���Ԫ��
     */
    public function lemmatize(string $word, ?string $language = null): string;
}
