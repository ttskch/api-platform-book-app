import { Container, Title } from "@mantine/core";
import createClient from "openapi-fetch";
import ArticleTable from "../components/article-table";
import { paths } from "../lib/api/schema";

// サーバー用のAPIクライアント
const client = createClient<paths, "application/ld+json">({
  baseUrl: "http://localhost:8000",
  headers: {
    "Content-Type": "application/ld+json",
  },
});

const defaultItemsPerPage = 10;

type Props = {
  searchParams: Promise<{
    page?: string | string[];
    itemsPerPage?: string | string[];
  }>;
};

export default async function HomePage({ searchParams }: Props) {
  const { page: rawPage, itemsPerPage: rawItemsPerPage } = await searchParams;
  const page = Number(Array.isArray(rawPage) ? rawPage[0] : rawPage) || 1;
  const itemsPerPage =
    Number(
      Array.isArray(rawItemsPerPage) ? rawItemsPerPage[0] : rawItemsPerPage,
    ) || defaultItemsPerPage;

  const { data } = await client.GET("/api/articles", {
    params: { query: { page, itemsPerPage } },
  });
  const articles = data?.member ?? [];
  const totalItems = data?.totalItems ?? 1;

  return (
    <Container>
      <Title my="lg">ブログ記事一覧</Title>
      <ArticleTable
        initialArticles={articles}
        initialPage={page}
        initialItemsPerPage={itemsPerPage}
        initialTotalItems={totalItems}
      />
    </Container>
  );
}
