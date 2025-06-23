// AlingAi Pro Azure部署 - 主要基础设施配置
// 遵循Azure最佳实践，使用托管身份认证和最小权限原则

@description('应用程序名称')
param appName string = 'alingai-pro'

@description('环境名称 (dev, staging, prod)')
param environmentName string = 'prod'

@description('Azure区域')
param location string = resourceGroup().location

@description('数据库管理员用户名')
param databaseAdminUsername string = 'alingai_admin'

@description('数据库管理员密码')
@secure()
param databaseAdminPassword string

@description('AI服务API密钥')
@secure()
param deepSeekApiKey string

@description('邮件SMTP密码')
@secure()
param smtpPassword string

// 变量定义
var resourceNamePrefix = '${appName}-${environmentName}'
var tags = {
  application: appName
  environment: environmentName
  managedBy: 'bicep'
}

// App Service Plan - 支持PHP 8.1
resource appServicePlan 'Microsoft.Web/serverfarms@2023-01-01' = {
  name: '${resourceNamePrefix}-asp'
  location: location
  tags: tags
  sku: {
    name: 'P1v3'  // 生产级别，支持SSL和自定义域名
    capacity: 1
  }
  kind: 'linux'
  properties: {
    reserved: true
  }
}

// Web App - AlingAi Pro主应用
resource webApp 'Microsoft.Web/sites@2023-01-01' = {
  name: '${resourceNamePrefix}-webapp'
  location: location
  tags: tags
  identity: {
    type: 'SystemAssigned'  // 使用托管身份
  }
  properties: {
    serverFarmId: appServicePlan.id
    httpsOnly: true  // 强制HTTPS
    siteConfig: {
      linuxFxVersion: 'PHP|8.1'
      alwaysOn: true
      http20Enabled: true
      minTlsVersion: '1.2'
      ftpsState: 'Disabled'
      appSettings: [
        {
          name: 'APP_NAME'
          value: 'AlingAi Pro'
        }
        {
          name: 'APP_ENV'
          value: environmentName
        }
        {
          name: 'APP_DEBUG'
          value: environmentName == 'prod' ? 'false' : 'true'
        }
        {
          name: 'APP_URL'
          value: 'https://${resourceNamePrefix}-webapp.azurewebsites.net'
        }
        {
          name: 'DB_CONNECTION'
          value: 'mysql'
        }
        {
          name: 'DB_HOST'
          value: mysqlServer.properties.fullyQualifiedDomainName
        }
        {
          name: 'DB_PORT'
          value: '3306'
        }
        {
          name: 'DB_DATABASE'
          value: 'alingai'
        }
        {
          name: 'DB_USERNAME'
          value: databaseAdminUsername
        }
        {
          name: 'DB_PASSWORD'
          value: '@Microsoft.KeyVault(VaultName=${keyVault.name};SecretName=database-password)'
        }
        {
          name: 'DEEPSEEK_API_KEY'
          value: '@Microsoft.KeyVault(VaultName=${keyVault.name};SecretName=deepseek-api-key)'
        }
        {
          name: 'MAIL_MAILER'
          value: 'smtp'
        }
        {
          name: 'MAIL_HOST'
          value: 'smtp.exmail.qq.com'
        }
        {
          name: 'MAIL_PORT'
          value: '465'
        }
        {
          name: 'MAIL_USERNAME'
          value: 'admin@gxggm.com'
        }
        {
          name: 'MAIL_PASSWORD'
          value: '@Microsoft.KeyVault(VaultName=${keyVault.name};SecretName=smtp-password)'
        }
        {
          name: 'MAIL_ENCRYPTION'
          value: 'ssl'
        }
      ]
      phpVersion: '8.1'
    }
  }
}

// MySQL数据库服务器
resource mysqlServer 'Microsoft.DBforMySQL/flexibleServers@2023-06-30' = {
  name: '${resourceNamePrefix}-mysql'
  location: location
  tags: tags
  sku: {
    name: 'Standard_B1ms'  // 基本层，适合小到中型应用
    tier: 'Burstable'
  }
  properties: {
    administratorLogin: databaseAdminUsername
    administratorLoginPassword: databaseAdminPassword
    version: '8.0'
    storage: {
      storageSize: 20
      iops: 360
      autoGrow: 'Enabled'
    }
    backup: {
      backupRetentionDays: 7
      geoRedundantBackup: 'Disabled'
    }
    highAvailability: {
      mode: 'Disabled'  // 生产环境可启用ZoneRedundant
    }
  }
}

// MySQL数据库
resource mysqlDatabase 'Microsoft.DBforMySQL/flexibleServers/databases@2023-06-30' = {
  parent: mysqlServer
  name: 'alingai'
  properties: {
    charset: 'utf8mb4'
    collation: 'utf8mb4_unicode_ci'
  }
}

// 防火墙规则 - 允许Azure服务访问
resource mysqlFirewallRule 'Microsoft.DBforMySQL/flexibleServers/firewallRules@2023-06-30' = {
  parent: mysqlServer
  name: 'AllowAzureServices'
  properties: {
    startIpAddress: '0.0.0.0'
    endIpAddress: '0.0.0.0'
  }
}

// Key Vault - 存储敏感配置
resource keyVault 'Microsoft.KeyVault/vaults@2023-07-01' = {
  name: '${resourceNamePrefix}-kv'
  location: location
  tags: tags
  properties: {
    sku: {
      family: 'A'
      name: 'standard'
    }
    tenantId: tenant().tenantId
    enabledForDeployment: false
    enabledForTemplateDeployment: true
    enabledForDiskEncryption: false
    enableRbacAuthorization: true
    publicNetworkAccess: 'Enabled'
    networkAcls: {
      defaultAction: 'Allow'
      bypass: 'AzureServices'
    }
  }
}

// Key Vault访问策略 - Web App托管身份
resource keyVaultAccessPolicy 'Microsoft.KeyVault/vaults/accessPolicies@2023-07-01' = {
  parent: keyVault
  name: 'add'
  properties: {
    accessPolicies: [
      {
        tenantId: tenant().tenantId
        objectId: webApp.identity.principalId
        permissions: {
          secrets: [
            'get'
            'list'
          ]
        }
      }
    ]
  }
}

// Key Vault秘密 - 数据库密码
resource databasePasswordSecret 'Microsoft.KeyVault/vaults/secrets@2023-07-01' = {
  parent: keyVault
  name: 'database-password'
  properties: {
    value: databaseAdminPassword
  }
}

// Key Vault秘密 - DeepSeek API密钥
resource deepSeekApiKeySecret 'Microsoft.KeyVault/vaults/secrets@2023-07-01' = {
  parent: keyVault
  name: 'deepseek-api-key'
  properties: {
    value: deepSeekApiKey
  }
}

// Key Vault秘密 - SMTP密码
resource smtpPasswordSecret 'Microsoft.KeyVault/vaults/secrets@2023-07-01' = {
  parent: keyVault
  name: 'smtp-password'
  properties: {
    value: smtpPassword
  }
}

// 存储账户 - 文件上传和日志
resource storageAccount 'Microsoft.Storage/storageAccounts@2023-01-01' = {
  name: replace('${resourceNamePrefix}storage', '-', '')  // 存储账户名不能包含连字符
  location: location
  tags: tags
  sku: {
    name: 'Standard_LRS'
  }
  kind: 'StorageV2'
  properties: {
    minimumTlsVersion: 'TLS1_2'
    allowBlobPublicAccess: false
    supportsHttpsTrafficOnly: true
    accessTier: 'Hot'
  }
}

// Blob容器 - 上传文件
resource uploadsContainer 'Microsoft.Storage/storageAccounts/blobServices/containers@2023-01-01' = {
  name: '${storageAccount.name}/default/uploads'
  properties: {
    publicAccess: 'None'
  }
}

// 应用洞察 - 监控和诊断
resource applicationInsights 'Microsoft.Insights/components@2020-02-02' = {
  name: '${resourceNamePrefix}-insights'
  location: location
  tags: tags
  kind: 'web'
  properties: {
    Application_Type: 'web'
    Request_Source: 'rest'
  }
}

// Log Analytics工作区
resource logAnalyticsWorkspace 'Microsoft.OperationalInsights/workspaces@2023-09-01' = {
  name: '${resourceNamePrefix}-logs'
  location: location
  tags: tags
  properties: {
    sku: {
      name: 'PerGB2018'
    }
    retentionInDays: 30
  }
}

// 输出重要信息
output webAppUrl string = 'https://${webApp.properties.defaultHostName}'
output databaseHost string = mysqlServer.properties.fullyQualifiedDomainName
output keyVaultUri string = keyVault.properties.vaultUri
output storageAccountName string = storageAccount.name
output applicationInsightsInstrumentationKey string = applicationInsights.properties.InstrumentationKey
