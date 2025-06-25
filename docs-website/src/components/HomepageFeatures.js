import React from 'react';
import clsx from 'clsx';
import styles from './HomepageFeatures.module.css';

const FeatureList = [
  {
    title: '编码标准',
    Svg: require('../../static/img/undraw_docusaurus_mountain.svg').default,
    description: (
      <>
        包含PHP命名空间规范、最佳实践指南和中文编码标准，
        确保代码质量和一致性。
      </>
    ),
  },
  {
    title: '开发指南',
    Svg: require('../../static/img/undraw_docusaurus_tree.svg').default,
    description: (
      <>
        提供PHP开发流程、代码质量自动化工具使用方法、
        项目维护计划和错误修复指南。
      </>
    ),
  },
  {
    title: '技术参考',
    Svg: require('../../static/img/undraw_docusaurus_react.svg').default,
    description: (
      <>
        提供PHP 8.1语法错误常见修复方法、Unicode编码建议
        和项目架构概述等技术参考资料。
      </>
    ),
  },
];

function Feature({Svg, title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Svg className={styles.featureSvg} alt={title} />
      </div>
      <div className="text--center padding-horiz--md">
        <h3>{title}</h3>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
