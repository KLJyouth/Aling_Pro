import React from 'react';
import clsx from 'clsx';
import styles from './HomepageFeatures.module.css';

const FeatureList = [
  {
    title: 'Coding Standards',
    Svg: require('../../static/img/undraw_docusaurus_mountain.svg').default,
    description: (
      <>
        Includes PHP namespace standards, best practices guide, 
        and Chinese encoding standards to ensure code quality and consistency.
      </>
    ),
  },
  {
    title: 'Development Guides',
    Svg: require('../../static/img/undraw_docusaurus_tree.svg').default,
    description: (
      <>
        Provides PHP development processes, code quality automation tools usage,
        project maintenance plans, and error fixing guides.
      </>
    ),
  },
  {
    title: 'Technical References',
    Svg: require('../../static/img/undraw_docusaurus_react.svg').default,
    description: (
      <>
        Provides common fixes for PHP 8.1 syntax errors, Unicode encoding recommendations,
        and project architecture overview.
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