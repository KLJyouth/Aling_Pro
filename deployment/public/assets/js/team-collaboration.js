/**
 * AlingAi 集成检测系统 - 团队协作功能模块
 * 实现多用户支持、分享功能、基于角色的访问控制、团队工作空间等企业级团队协作特性
 * 版本: 1.0.0
 * 创建时间: 2024-12-19
 */

class TeamCollaboration {
    constructor() {
        this.currentUser = null;
        this.currentTeam = null;
        this.permissions = new Set();
        this.collaborators = new Map();
        this.sharedSessions = new Map();
        this.realTimeUpdates = new Set();
        this.teamSettings = {
            allowGuestAccess: false,
            requireApprovalForSharing: true,
            enableRealTimeCollab: true,
            maxCollaborators: 10,
            sessionTimeoutMinutes: 30
        };
        
        // 预定义角色和权限
        this.rolePermissions = new Map([
            ['owner', new Set(['all'])],
            ['admin', new Set(['manage_team', 'manage_sessions', 'invite_users', 'view_analytics', 'edit_settings'])],
            ['editor', new Set(['create_sessions', 'edit_sessions', 'share_sessions', 'view_sessions'])],
            ['viewer', new Set(['view_sessions', 'comment_sessions'])],
            ['guest', new Set(['view_shared_sessions'])]
        ]);

        this.init();
    }

    /**
     * 初始化团队协作系统
     */
    init() {
        console.log('🤝 正在初始化团队协作系统...');
        
        try {
            this.loadUserProfile();
            this.loadTeamData();
            this.setupEventListeners();
            this.initializeWebSocket();
            this.startHeartbeat();
            
            console.log('✅ 团队协作系统初始化完成');
        } catch (error) {
            console.error('❌ 团队协作系统初始化失败:', error);
        }
    }

    /**
     * 加载用户档案
     */
    loadUserProfile() {
        const userData = localStorage.getItem('currentUser');
        if (userData) {
            this.currentUser = JSON.parse(userData);
            this.loadUserPermissions();
        }
    }

    /**
     * 加载用户权限
     */
    loadUserPermissions() {
        if (!this.currentUser) return;
        
        const userRole = this.currentUser.role || 'viewer';
        const rolePerms = this.rolePermissions.get(userRole) || new Set();
        this.permissions = new Set([...rolePerms]);
        
        // 如果是所有者或管理员，添加额外权限
        if (userRole === 'owner' || this.permissions.has('all')) {
            this.permissions = new Set(['all']);
        }
    }

    /**
     * 权限检查
     */
    hasPermission(permission) {
        return this.permissions.has('all') || this.permissions.has(permission);
    }

    /**
     * 加载团队数据
     */
    loadTeamData() {
        const teamData = localStorage.getItem('currentTeam');
        if (teamData) {
            this.currentTeam = JSON.parse(teamData);
            this.loadCollaborators();
            this.loadSharedSessions();
        }
    }

    /**
     * 加载协作者列表
     */
    loadCollaborators() {
        const collabData = localStorage.getItem('teamCollaborators');
        if (collabData) {
            const collabArray = JSON.parse(collabData);
            collabArray.forEach(collab => {
                this.collaborators.set(collab.userId, collab);
            });
        }
    }

    /**
     * 加载共享会话
     */
    loadSharedSessions() {
        const sharedData = localStorage.getItem('sharedSessions');
        if (sharedData) {
            const sharedArray = JSON.parse(sharedData);
            sharedArray.forEach(session => {
                this.sharedSessions.set(session.sessionId, session);
            });
        }
    }

    /**
     * 设置事件监听器
     */
    setupEventListeners() {
        // 监听检测事件，实现实时协作
        document.addEventListener('detectionStarted', (event) => {
            this.broadcastDetectionEvent('started', event.detail);
        });

        document.addEventListener('detectionCompleted', (event) => {
            this.broadcastDetectionEvent('completed', event.detail);
        });

        document.addEventListener('testStatusUpdated', (event) => {
            this.broadcastTestUpdate(event.detail);
        });

        // 页面卸载时清理
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });
    }

    /**
     * 初始化WebSocket连接（模拟实时通信）
     */
    initializeWebSocket() {
        // 模拟WebSocket连接，实际项目中应该连接真实的WebSocket服务器
        this.wsConnected = true;
        console.log('🔗 WebSocket连接已建立（模拟）');
        
        // 模拟接收实时消息
        setInterval(() => {
            if (this.wsConnected && this.realTimeUpdates.size > 0) {
                this.processRealTimeUpdates();
            }
        }, 2000);
    }

    /**
     * 处理实时更新
     */
    processRealTimeUpdates() {
        this.realTimeUpdates.forEach(update => {
            this.handleRealTimeUpdate(update);
        });
        this.realTimeUpdates.clear();
    }

    /**
     * 处理实时更新事件
     */
    handleRealTimeUpdate(update) {
        switch (update.type) {
            case 'user_joined':
                this.onUserJoined(update.data);
                break;
            case 'detection_shared':
                this.onDetectionShared(update.data);
                break;
            case 'permission_changed':
                this.onPermissionChanged(update.data);
                break;
            case 'comment_added':
                this.onCommentAdded(update.data);
                break;
        }
    }

    /**
     * 创建团队工作空间
     */
    async createTeamWorkspace(teamName, description = '') {
        if (!this.hasPermission('manage_team')) {
            throw new Error('没有创建团队工作空间的权限');
        }

        const teamId = `team_${Date.now()}_${Math.random().toString(36).substring(2, 8)}`;
        
        const team = {
            id: teamId,
            name: teamName,
            description: description,
            ownerId: this.currentUser.id,
            createdAt: new Date().toISOString(),
            settings: { ...this.teamSettings },
            members: [
                {
                    userId: this.currentUser.id,
                    username: this.currentUser.username,
                    role: 'owner',
                    joinedAt: new Date().toISOString(),
                    status: 'active'
                }
            ]
        };

        this.currentTeam = team;
        localStorage.setItem('currentTeam', JSON.stringify(team));
        
        this.showNotification('success', `团队工作空间 "${teamName}" 创建成功`);
        return team;
    }

    /**
     * 邀请用户加入团队
     */
    async inviteUserToTeam(userEmail, role = 'viewer') {
        if (!this.hasPermission('invite_users')) {
            throw new Error('没有邀请用户的权限');
        }

        if (!this.currentTeam) {
            throw new Error('未加入任何团队');
        }

        // 模拟发送邀请
        const inviteId = `invite_${Date.now()}_${Math.random().toString(36).substring(2, 8)}`;
        
        const invitation = {
            id: inviteId,
            teamId: this.currentTeam.id,
            teamName: this.currentTeam.name,
            inviterName: this.currentUser.username,
            email: userEmail,
            role: role,
            status: 'pending',
            createdAt: new Date().toISOString(),
            expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString() // 7天后过期
        };

        // 保存邀请记录
        const invitations = JSON.parse(localStorage.getItem('teamInvitations') || '[]');
        invitations.push(invitation);
        localStorage.setItem('teamInvitations', JSON.stringify(invitations));

        this.showNotification('success', `已向 ${userEmail} 发送团队邀请`);
        
        // 模拟实时通知
        this.broadcastTeamEvent('user_invited', {
            invitation: invitation,
            inviter: this.currentUser.username
        });

        return invitation;
    }

    /**
     * 接受团队邀请
     */
    async acceptTeamInvitation(inviteId) {
        const invitations = JSON.parse(localStorage.getItem('teamInvitations') || '[]');
        const invitation = invitations.find(inv => inv.id === inviteId && inv.status === 'pending');

        if (!invitation) {
            throw new Error('邀请不存在或已过期');
        }

        // 检查邀请是否过期
        if (new Date() > new Date(invitation.expiresAt)) {
            throw new Error('邀请已过期');
        }

        // 加入团队
        const member = {
            userId: this.currentUser.id,
            username: this.currentUser.username,
            email: invitation.email,
            role: invitation.role,
            joinedAt: new Date().toISOString(),
            status: 'active'
        };

        // 更新团队成员列表
        if (this.currentTeam && this.currentTeam.id === invitation.teamId) {
            this.currentTeam.members.push(member);
            localStorage.setItem('currentTeam', JSON.stringify(this.currentTeam));
        }

        // 更新邀请状态
        invitation.status = 'accepted';
        localStorage.setItem('teamInvitations', JSON.stringify(invitations));

        // 更新用户权限
        this.loadUserPermissions();

        this.showNotification('success', `已成功加入团队 "${invitation.teamName}"`);
        
        // 广播用户加入事件
        this.broadcastTeamEvent('user_joined', {
            user: member,
            team: invitation.teamName
        });

        return member;
    }

    /**
     * 分享检测会话
     */
    async shareDetectionSession(sessionId, shareOptions = {}) {
        if (!this.hasPermission('share_sessions')) {
            throw new Error('没有分享会话的权限');
        }

        const defaultOptions = {
            allowEdit: false,
            allowComment: true,
            expiresAt: null,
            shareWith: 'team', // 'team', 'public', 'specific'
            specificUsers: [],
            requireAuth: true
        };

        const options = { ...defaultOptions, ...shareOptions };
        
        const shareId = `share_${Date.now()}_${Math.random().toString(36).substring(2, 8)}`;
        
        const sharedSession = {
            shareId: shareId,
            sessionId: sessionId,
            ownerId: this.currentUser.id,
            ownerName: this.currentUser.username,
            createdAt: new Date().toISOString(),
            ...options
        };

        this.sharedSessions.set(sessionId, sharedSession);
        
        // 保存到本地存储
        const sharedArray = Array.from(this.sharedSessions.values());
        localStorage.setItem('sharedSessions', JSON.stringify(sharedArray));

        this.showNotification('success', '检测会话分享成功');
        
        // 广播分享事件
        this.broadcastTeamEvent('detection_shared', {
            sessionId: sessionId,
            sharedBy: this.currentUser.username,
            shareOptions: options
        });

        return sharedSession;
    }

    /**
     * 访问共享会话
     */
    async accessSharedSession(shareId) {
        const session = Array.from(this.sharedSessions.values()).find(s => s.shareId === shareId);
        
        if (!session) {
            throw new Error('共享会话不存在');
        }

        // 检查访问权限
        if (!this.canAccessSharedSession(session)) {
            throw new Error('没有访问此共享会话的权限');
        }

        // 检查过期时间
        if (session.expiresAt && new Date() > new Date(session.expiresAt)) {
            throw new Error('共享会话已过期');
        }

        // 记录访问日志
        this.logSessionAccess(session);

        return session;
    }

    /**
     * 检查是否可以访问共享会话
     */
    canAccessSharedSession(session) {
        // 所有者总是可以访问
        if (session.ownerId === this.currentUser?.id) {
            return true;
        }

        // 检查分享类型
        switch (session.shareWith) {
            case 'public':
                return true;
            case 'team':
                return this.currentTeam && this.isTeamMember();
            case 'specific':
                return session.specificUsers.includes(this.currentUser?.id);
            default:
                return false;
        }
    }

    /**
     * 检查是否是团队成员
     */
    isTeamMember() {
        if (!this.currentTeam || !this.currentUser) return false;
        return this.currentTeam.members.some(member => member.userId === this.currentUser.id);
    }

    /**
     * 添加评论到共享会话
     */
    async addCommentToSession(sessionId, comment) {
        if (!this.hasPermission('comment_sessions')) {
            throw new Error('没有评论权限');
        }

        const commentId = `comment_${Date.now()}_${Math.random().toString(36).substring(2, 8)}`;
        
        const commentData = {
            id: commentId,
            sessionId: sessionId,
            userId: this.currentUser.id,
            username: this.currentUser.username,
            content: comment,
            createdAt: new Date().toISOString(),
            reactions: {},
            replies: []
        };

        // 保存评论
        const comments = JSON.parse(localStorage.getItem('sessionComments') || '[]');
        comments.push(commentData);
        localStorage.setItem('sessionComments', JSON.stringify(comments));

        this.showNotification('success', '评论添加成功');
        
        // 广播评论事件
        this.broadcastTeamEvent('comment_added', {
            sessionId: sessionId,
            comment: commentData
        });

        return commentData;
    }

    /**
     * 获取会话评论
     */
    getSessionComments(sessionId) {
        const comments = JSON.parse(localStorage.getItem('sessionComments') || '[]');
        return comments.filter(comment => comment.sessionId === sessionId);
    }

    /**
     * 管理团队成员角色
     */
    async updateMemberRole(userId, newRole) {
        if (!this.hasPermission('manage_team')) {
            throw new Error('没有管理团队的权限');
        }

        if (!this.currentTeam) {
            throw new Error('未加入任何团队');
        }

        const member = this.currentTeam.members.find(m => m.userId === userId);
        if (!member) {
            throw new Error('成员不存在');
        }

        const oldRole = member.role;
        member.role = newRole;
        member.updatedAt = new Date().toISOString();

        // 保存更新
        localStorage.setItem('currentTeam', JSON.stringify(this.currentTeam));

        this.showNotification('success', `成员 ${member.username} 的角色已更新为 ${newRole}`);
        
        // 广播权限变更事件
        this.broadcastTeamEvent('permission_changed', {
            userId: userId,
            username: member.username,
            oldRole: oldRole,
            newRole: newRole,
            updatedBy: this.currentUser.username
        });

        return member;
    }

    /**
     * 移除团队成员
     */
    async removeMember(userId) {
        if (!this.hasPermission('manage_team')) {
            throw new Error('没有管理团队的权限');
        }

        if (!this.currentTeam) {
            throw new Error('未加入任何团队');
        }

        const memberIndex = this.currentTeam.members.findIndex(m => m.userId === userId);
        if (memberIndex === -1) {
            throw new Error('成员不存在');
        }

        const member = this.currentTeam.members[memberIndex];
        
        // 不能移除所有者
        if (member.role === 'owner') {
            throw new Error('不能移除团队所有者');
        }

        this.currentTeam.members.splice(memberIndex, 1);
        localStorage.setItem('currentTeam', JSON.stringify(this.currentTeam));

        this.showNotification('success', `成员 ${member.username} 已被移除`);
        
        // 广播成员移除事件
        this.broadcastTeamEvent('member_removed', {
            userId: userId,
            username: member.username,
            removedBy: this.currentUser.username
        });

        return true;
    }

    /**
     * 获取团队统计信息
     */
    getTeamAnalytics() {
        if (!this.hasPermission('view_analytics')) {
            throw new Error('没有查看分析的权限');
        }

        const comments = JSON.parse(localStorage.getItem('sessionComments') || '[]');
        const sharedSessions = Array.from(this.sharedSessions.values());
        
        return {
            teamInfo: {
                name: this.currentTeam?.name || '未知团队',
                memberCount: this.currentTeam?.members.length || 0,
                createdAt: this.currentTeam?.createdAt
            },
            activity: {
                totalSharedSessions: sharedSessions.length,
                totalComments: comments.length,
                activeMembers: this.getActiveMemberCount(),
                recentActivity: this.getRecentActivity()
            },
            permissions: {
                userRole: this.currentUser?.role || 'guest',
                availablePermissions: Array.from(this.permissions)
            }
        };
    }

    /**
     * 获取活跃成员数量
     */
    getActiveMemberCount() {
        if (!this.currentTeam) return 0;
        
        const now = new Date();
        const oneDayAgo = new Date(now.getTime() - 24 * 60 * 60 * 1000);
        
        return this.currentTeam.members.filter(member => {
            const lastActive = member.lastActive ? new Date(member.lastActive) : new Date(member.joinedAt);
            return lastActive > oneDayAgo;
        }).length;
    }

    /**
     * 获取最近活动
     */
    getRecentActivity() {
        const activities = [];
        
        // 获取最近的评论
        const comments = JSON.parse(localStorage.getItem('sessionComments') || '[]')
            .slice(-5)
            .map(comment => ({
                type: 'comment',
                user: comment.username,
                time: comment.createdAt,
                description: `在会话中添加了评论`
            }));

        // 获取最近的分享
        const shares = Array.from(this.sharedSessions.values())
            .slice(-5)
            .map(share => ({
                type: 'share',
                user: share.ownerName,
                time: share.createdAt,
                description: `分享了检测会话`
            }));

        activities.push(...comments, ...shares);
        
        // 按时间排序，返回最近的10个活动
        return activities
            .sort((a, b) => new Date(b.time) - new Date(a.time))
            .slice(0, 10);
    }

    /**
     * 广播检测事件
     */
    broadcastDetectionEvent(eventType, data) {
        if (!this.teamSettings.enableRealTimeCollab) return;

        const event = {
            type: `detection_${eventType}`,
            userId: this.currentUser?.id,
            username: this.currentUser?.username,
            timestamp: new Date().toISOString(),
            data: data
        };

        this.realTimeUpdates.add(event);
        
        // 实际项目中这里应该通过WebSocket发送到服务器
        console.log('🔄 广播检测事件:', event);
    }

    /**
     * 广播测试更新
     */
    broadcastTestUpdate(testData) {
        if (!this.teamSettings.enableRealTimeCollab) return;

        const event = {
            type: 'test_update',
            userId: this.currentUser?.id,
            username: this.currentUser?.username,
            timestamp: new Date().toISOString(),
            data: testData
        };

        this.realTimeUpdates.add(event);
        console.log('🔄 广播测试更新:', event);
    }

    /**
     * 广播团队事件
     */
    broadcastTeamEvent(eventType, data) {
        const event = {
            type: eventType,
            userId: this.currentUser?.id,
            username: this.currentUser?.username,
            teamId: this.currentTeam?.id,
            timestamp: new Date().toISOString(),
            data: data
        };

        this.realTimeUpdates.add(event);
        console.log('🔄 广播团队事件:', event);
    }

    /**
     * 事件处理函数
     */
    onUserJoined(data) {
        this.showNotification('info', `${data.user.username} 加入了团队`);
    }

    onDetectionShared(data) {
        this.showNotification('info', `${data.sharedBy} 分享了一个检测会话`);
    }

    onPermissionChanged(data) {
        this.showNotification('info', `${data.username} 的角色已更新为 ${data.newRole}`);
    }

    onCommentAdded(data) {
        this.showNotification('info', `${data.comment.username} 添加了新评论`);
    }

    /**
     * 记录会话访问日志
     */
    logSessionAccess(session) {
        const accessLog = {
            shareId: session.shareId,
            sessionId: session.sessionId,
            userId: this.currentUser?.id,
            username: this.currentUser?.username,
            accessTime: new Date().toISOString(),
            userAgent: navigator.userAgent
        };

        const logs = JSON.parse(localStorage.getItem('sessionAccessLogs') || '[]');
        logs.push(accessLog);
        
        // 只保留最近1000条记录
        if (logs.length > 1000) {
            logs.splice(0, logs.length - 1000);
        }
        
        localStorage.setItem('sessionAccessLogs', JSON.stringify(logs));
    }

    /**
     * 开始心跳检测
     */
    startHeartbeat() {
        setInterval(() => {
            if (this.currentUser && this.currentTeam) {
                this.updateUserActivity();
            }
        }, 30000); // 每30秒更新一次活动状态
    }

    /**
     * 更新用户活动状态
     */
    updateUserActivity() {
        if (!this.currentTeam || !this.currentUser) return;

        const member = this.currentTeam.members.find(m => m.userId === this.currentUser.id);
        if (member) {
            member.lastActive = new Date().toISOString();
            localStorage.setItem('currentTeam', JSON.stringify(this.currentTeam));
        }
    }

    /**
     * 显示通知
     */
    showNotification(type, message) {
        // 集成现有的通知系统
        if (window.notificationSystem) {
            window.notificationSystem.show(type, message);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    /**
     * 清理资源
     */
    cleanup() {
        if (this.wsConnected) {
            this.wsConnected = false;
            console.log('🔌 WebSocket连接已断开');
        }
        
        this.realTimeUpdates.clear();
    }

    /**
     * 导出团队数据
     */
    exportTeamData() {
        if (!this.hasPermission('view_analytics')) {
            throw new Error('没有导出数据的权限');
        }

        const exportData = {
            team: this.currentTeam,
            sharedSessions: Array.from(this.sharedSessions.values()),
            comments: JSON.parse(localStorage.getItem('sessionComments') || '[]'),
            analytics: this.getTeamAnalytics(),
            exportedAt: new Date().toISOString(),
            exportedBy: this.currentUser?.username
        };

        return exportData;
    }

    /**
     * 获取团队协作界面
     */
    getCollaborationUI() {
        return `
            <div class="team-collaboration-panel">
                <div class="collaboration-header">
                    <h3><i class="bi bi-people-fill"></i> 团队协作</h3>
                    <div class="team-info">
                        <span class="team-name">${this.currentTeam?.name || '未加入团队'}</span>
                        <span class="member-count">${this.currentTeam?.members.length || 0} 成员</span>
                    </div>
                </div>

                <div class="collaboration-tabs">
                    <div class="tab-buttons">
                        <button class="tab-btn active" data-tab="members">团队成员</button>
                        <button class="tab-btn" data-tab="shared">共享会话</button>
                        <button class="tab-btn" data-tab="activity">活动记录</button>
                        <button class="tab-btn" data-tab="settings">团队设置</button>
                    </div>

                    <div class="tab-content">
                        <div class="tab-panel active" id="members-panel">
                            ${this.getMembersPanel()}
                        </div>
                        <div class="tab-panel" id="shared-panel">
                            ${this.getSharedSessionsPanel()}
                        </div>
                        <div class="tab-panel" id="activity-panel">
                            ${this.getActivityPanel()}
                        </div>
                        <div class="tab-panel" id="settings-panel">
                            ${this.getSettingsPanel()}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * 获取成员面板
     */
    getMembersPanel() {
        if (!this.currentTeam) {
            return '<p class="no-data">未加入任何团队</p>';
        }

        const members = this.currentTeam.members.map(member => `
            <div class="member-item">
                <div class="member-info">
                    <div class="member-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="member-details">
                        <div class="member-name">${member.username}</div>
                        <div class="member-role">${member.role}</div>
                        <div class="member-joined">加入时间: ${new Date(member.joinedAt).toLocaleString()}</div>
                    </div>
                </div>
                <div class="member-actions">
                    ${this.hasPermission('manage_team') && member.role !== 'owner' ? `
                        <button class="btn btn-sm btn-outline-primary" onclick="teamCollaboration.showMemberActions('${member.userId}')">
                            <i class="bi bi-gear"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `).join('');

        return `
            <div class="members-panel">
                ${this.hasPermission('invite_users') ? `
                    <div class="invite-section">
                        <button class="btn btn-primary" onclick="teamCollaboration.showInviteModal()">
                            <i class="bi bi-plus-circle"></i> 邀请成员
                        </button>
                    </div>
                ` : ''}
                <div class="members-list">
                    ${members}
                </div>
            </div>
        `;
    }

    /**
     * 获取共享会话面板
     */
    getSharedSessionsPanel() {
        const sessions = Array.from(this.sharedSessions.values()).map(session => `
            <div class="shared-session-item">
                <div class="session-info">
                    <div class="session-title">检测会话 ${session.sessionId.substring(0, 8)}...</div>
                    <div class="session-owner">分享者: ${session.ownerName}</div>
                    <div class="session-created">分享时间: ${new Date(session.createdAt).toLocaleString()}</div>
                </div>
                <div class="session-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="teamCollaboration.openSharedSession('${session.shareId}')">
                        <i class="bi bi-eye"></i> 查看
                    </button>
                    ${session.ownerId === this.currentUser?.id ? `
                        <button class="btn btn-sm btn-outline-secondary" onclick="teamCollaboration.manageSharedSession('${session.shareId}')">
                            <i class="bi bi-gear"></i> 管理
                        </button>
                    ` : ''}
                </div>
            </div>
        `).join('');

        return `
            <div class="shared-sessions-panel">
                ${this.hasPermission('share_sessions') ? `
                    <div class="share-section">
                        <button class="btn btn-primary" onclick="teamCollaboration.showShareModal()">
                            <i class="bi bi-share"></i> 分享当前会话
                        </button>
                    </div>
                ` : ''}
                <div class="sessions-list">
                    ${sessions.length > 0 ? sessions : '<p class="no-data">暂无共享会话</p>'}
                </div>
            </div>
        `;
    }

    /**
     * 获取活动面板
     */
    getActivityPanel() {
        const activities = this.getRecentActivity().map(activity => `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="bi bi-${activity.type === 'comment' ? 'chat-dots' : 'share'}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">
                        <strong>${activity.user}</strong> ${activity.description}
                    </div>
                    <div class="activity-time">${new Date(activity.time).toLocaleString()}</div>
                </div>
            </div>
        `).join('');

        return `
            <div class="activity-panel">
                <div class="activity-list">
                    ${activities.length > 0 ? activities : '<p class="no-data">暂无活动记录</p>'}
                </div>
            </div>
        `;
    }

    /**
     * 获取设置面板
     */
    getSettingsPanel() {
        if (!this.hasPermission('edit_settings')) {
            return '<p class="no-permission">没有编辑设置的权限</p>';
        }

        return `
            <div class="settings-panel">
                <div class="setting-group">
                    <h5>访问控制</h5>
                    <div class="setting-item">
                        <label>
                            <input type="checkbox" ${this.teamSettings.allowGuestAccess ? 'checked' : ''} 
                                   onchange="teamCollaboration.updateSetting('allowGuestAccess', this.checked)">
                            允许访客访问
                        </label>
                    </div>
                    <div class="setting-item">
                        <label>
                            <input type="checkbox" ${this.teamSettings.requireApprovalForSharing ? 'checked' : ''} 
                                   onchange="teamCollaboration.updateSetting('requireApprovalForSharing', this.checked)">
                            分享需要审批
                        </label>
                    </div>
                </div>

                <div class="setting-group">
                    <h5>协作功能</h5>
                    <div class="setting-item">
                        <label>
                            <input type="checkbox" ${this.teamSettings.enableRealTimeCollab ? 'checked' : ''} 
                                   onchange="teamCollaboration.updateSetting('enableRealTimeCollab', this.checked)">
                            启用实时协作
                        </label>
                    </div>
                    <div class="setting-item">
                        <label>
                            最大协作者数量:
                            <input type="number" min="1" max="50" value="${this.teamSettings.maxCollaborators}" 
                                   onchange="teamCollaboration.updateSetting('maxCollaborators', parseInt(this.value))">
                        </label>
                    </div>
                </div>

                <div class="setting-group">
                    <h5>会话管理</h5>
                    <div class="setting-item">
                        <label>
                            会话超时时间(分钟):
                            <input type="number" min="5" max="480" value="${this.teamSettings.sessionTimeoutMinutes}" 
                                   onchange="teamCollaboration.updateSetting('sessionTimeoutMinutes', parseInt(this.value))">
                        </label>
                    </div>
                </div>

                ${this.hasPermission('view_analytics') ? `
                    <div class="setting-group">
                        <h5>数据管理</h5>
                        <button class="btn btn-outline-primary" onclick="teamCollaboration.exportData()">
                            <i class="bi bi-download"></i> 导出团队数据
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }

    /**
     * 更新设置
     */
    updateSetting(key, value) {
        this.teamSettings[key] = value;
        if (this.currentTeam) {
            this.currentTeam.settings = { ...this.teamSettings };
            localStorage.setItem('currentTeam', JSON.stringify(this.currentTeam));
        }
        this.showNotification('success', '设置已更新');
    }

    /**
     * 导出数据
     */
    exportData() {
        try {
            const data = this.exportTeamData();
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `team-${this.currentTeam?.name || 'data'}-${new Date().toISOString().split('T')[0]}.json`;
            a.click();
            URL.revokeObjectURL(url);
            this.showNotification('success', '团队数据导出成功');
        } catch (error) {
            this.showNotification('error', '导出失败: ' + error.message);
        }
    }

    // 占位方法，实际实现中需要创建对应的模态框
    showInviteModal() {
        this.showNotification('info', '邀请功能面板（需要实现邀请模态框）');
    }

    showShareModal() {
        this.showNotification('info', '分享功能面板（需要实现分享模态框）');
    }

    showMemberActions(userId) {
        this.showNotification('info', `成员管理功能（需要实现成员操作面板）`);
    }

    openSharedSession(shareId) {
        this.showNotification('info', `打开共享会话: ${shareId}`);
    }

    manageSharedSession(shareId) {
        this.showNotification('info', `管理共享会话: ${shareId}`);
    }
}

// 全局实例
window.teamCollaboration = new TeamCollaboration();

// 导出模块
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TeamCollaboration;
}

console.log('🤝 团队协作模块已加载完成');
