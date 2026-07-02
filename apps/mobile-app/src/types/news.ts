export type NewsArticle = {
  id: string;
  slug: string;
  title: string;
  publishedAt: string;
  publishedAtLabel: string;
  image: number;
};

export type NewsArticleDetail = NewsArticle & {
  categoryName: string;
  description: string;
  images: number[];
};
